import 'dart:async';
import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';

import '../config/pusher_config.dart';
import 'api_client.dart';
import 'session_service.dart';

class PusherMessage {
  const PusherMessage({
    required this.channel,
    required this.name,
    required this.data,
    required this.message,
    required this.title,
    this.imageUrl,
    this.actionLabel,
  });

  final String channel;
  final String name;
  final Map<String, dynamic> data;
  final String message;
  final String title;
  final String? imageUrl;
  final String? actionLabel;
}

class PusherService {
  PusherService._();

  static final PusherService instance = PusherService._();

  final ValueNotifier<String> connectionState = ValueNotifier<String>('DISCONNECTED');
  final ValueNotifier<String?> lastError = ValueNotifier<String?>(null);
  final ValueNotifier<String?> lastEvent = ValueNotifier<String?>(null);
  final ValueNotifier<String?> lastMessageText = ValueNotifier<String?>(null);
  final ValueNotifier<List<String>> subscribedChannels = ValueNotifier<List<String>>(<String>[]);

  final StreamController<PusherMessage> _messages = StreamController<PusherMessage>.broadcast();
  final Set<String> _subscribedChannels = <String>{};
  final SessionService _sessionService = SessionService();
  final PusherChannelsFlutter _client = PusherChannelsFlutter.getInstance();

  bool _initialized = false;
  String? _lastAuthToken;
  Timer? _reconnectTimer;
  Timer? _reconnectKickTimer;

  Stream<PusherMessage> get messages => _messages.stream;
  bool get isConfigured => PusherConfig.isConfigured;

  Future<void> initialize() async {
    if (!isConfigured) {
      print('PUSHER no configurado: revisa `assets/runtime_config.json` o tus `--dart-define`.');
      return;
    }

    try {
      final token = await _sessionService.getToken();

      if (_initialized && _lastAuthToken == token) {
        // Si ya estaba inicializado pero quedó desconectado, intentamos reconectar.
        if (connectionState.value.toUpperCase() == 'DISCONNECTED') {
          try {
            _reconnectKickTimer?.cancel();
            _reconnectKickTimer = Timer(const Duration(milliseconds: 600), () async {
              try {
                await _client.connect();
              } catch (e) {
                lastError.value = 'connect: $e';
              }
            });
          } catch (e) {
            lastError.value = 'connect: $e';
          }
        }
        return;
      }
      if (_initialized && _lastAuthToken != token) {
        try {
          await _client.disconnect();
        } catch (_) {}
        _subscribedChannels.clear();
        _initialized = false;
      }

      final baseAuthEndpoint = PusherConfig.authEndpoint.trim().isEmpty
          ? '${ApiClient.dio.options.baseUrl}/pusher/auth'
          : PusherConfig.authEndpoint.trim();

      final authEndpoint = token.isEmpty
          ? baseAuthEndpoint
          : '$baseAuthEndpoint?token=${Uri.encodeQueryComponent(token)}';

      await _client.init(
        apiKey: PusherConfig.appKey,
        cluster: PusherConfig.cluster,
        useTLS: PusherConfig.useTls,
        logToConsole: true,
        authEndpoint: authEndpoint,
        // En Web, `authParams` puede causar type errors (LinkedMap) al cruzar a JS.
        // Usamos `authEndpoint` con `?token=` para autenticar sin headers.
        authParams: null,
        onAuthorizer: _authorizeChannel,
        onConnectionStateChange: (current, previous) {
          print('PUSHER state: $previous -> $current');
          connectionState.value = current.toUpperCase();
          if (current.toUpperCase() == 'DISCONNECTED') {
            _reconnectTimer ??= Timer.periodic(const Duration(seconds: 10), (_) async {
              if (!isConfigured) return;
              final state = connectionState.value.toUpperCase();
              if (state == 'CONNECTED' || state == 'CONNECTING') return;
              try {
                await _client.connect();
              } catch (e) {
                lastError.value = 'reconnect: $e';
              }
            });
          }
        },
        onSubscriptionError: (message, error) {
          print('PUSHER subscription error: $message error=$error');
          lastError.value = 'subscription: $message';
        },
        onError: (message, code, error) {
          print('PUSHER error: message=$message code=$code error=$error');
          lastError.value = '($code) $message';
        },
        onEvent: _handleEvent,
      );

      await _client.connect();
      _initialized = true;
      _lastAuthToken = token;
      lastError.value = null;
    } catch (e) {
      _initialized = false;
      lastError.value = 'init: $e';
      print('PUSHER init error: $e');
    }
  }

  Future<void> subscribeToChannel(String channelName) async {
    if (!isConfigured) {
      print('PUSHER subscribe ignorado (no configurado). channel=$channelName');
      return;
    }
    await initialize();
    if (!_initialized) return;
    if (connectionState.value.toUpperCase() == 'DISCONNECTED') {
      try {
        await _client.connect();
      } catch (e) {
        lastError.value = 'connect: $e';
      }
    }
    await _subscribe(channelName);
  }

  Future<void> syncSubscriptions() async {
    if (!isConfigured) {
      print('PUSHER syncSubscriptions ignorado (no configurado).');
      return;
    }

    await initialize();
    if (!_initialized) return;
    await _subscribe(PusherConfig.notificationsChannel);

    final token = await _sessionService.getToken();
    if (token.isEmpty) return;

    final userId = await _sessionService.getUserId();
    if (userId > 0) {
      await _subscribe(PusherConfig.userChannel(userId));
    }
  }

  Future<void> _subscribe(String channelName) async {
    final trimmed = channelName.trim();
    if (trimmed.isEmpty || _subscribedChannels.contains(trimmed)) return;

    await _client.subscribe(channelName: trimmed);
    _subscribedChannels.add(trimmed);
    subscribedChannels.value = _subscribedChannels.toList()..sort();
  }

  Future<Map<String, dynamic>> _authorizeChannel(
    String channelName,
    String socketId,
    dynamic _options,
  ) async {
    final token = await _sessionService.getToken();
    if (token.isEmpty) {
      throw StateError('No hay token para autorizar el canal: $channelName');
    }

    final res = await ApiClient.post<Map<String, dynamic>>(
      '/pusher/auth',
      data: <String, dynamic>{
        'socket_id': socketId,
        'channel_name': channelName,
      },
      options: Options(headers: {'Authorization': 'Bearer $token'}),
    );

    final data = (res.data ?? <String, dynamic>{}).cast<String, dynamic>();
    if (!data.containsKey('auth')) {
      throw StateError('Respuesta de auth inválida para Pusher: $data');
    }
    return data;
  }

  void _handleEvent(PusherEvent event) {
    if (event.eventName.startsWith('pusher:') || event.eventName.startsWith('pusher_internal:')) {
      return;
    }

    final payload = _normalizePayload(_decodePayload(event.data));
    final message = _extractMessage(payload, event.eventName);
    final title = _extractTitle(payload);

    lastEvent.value = '${event.channelName} • ${event.eventName}';
    lastMessageText.value = message.trim().isEmpty ? null : message.trim();

    _messages.add(
      PusherMessage(
        channel: event.channelName,
        name: event.eventName,
        data: payload,
        message: message,
        title: title,
        imageUrl: _extractOptional(payload, const ['image_url', 'image', 'banner']),
        actionLabel: _extractOptional(payload, const ['cta_label', 'action_label', 'button_text']),
      ),
    );
  }

  Map<String, dynamic> _normalizePayload(Map<String, dynamic> payload) {
    for (final key in const ['data', 'payload']) {
      final nested = payload[key];
      if (nested is Map) {
        return nested.cast<String, dynamic>();
      }
      if (nested is String && nested.trim().isNotEmpty) {
        try {
          final decoded = jsonDecode(nested);
          if (decoded is Map<String, dynamic>) {
            return decoded;
          }
        } catch (_) {
          // Si no es JSON valido, seguimos con el payload plano.
        }
      }
    }
    return payload;
  }

  Map<String, dynamic> _decodePayload(dynamic raw) {
    if (raw is Map) {
      try {
        return raw.cast<String, dynamic>();
      } catch (_) {
        return <String, dynamic>{};
      }
    }
    if (raw is Map<String, dynamic>) return raw;
    if (raw is String && raw.trim().isNotEmpty) {
      try {
        final decoded = jsonDecode(raw);
        if (decoded is Map<String, dynamic>) return decoded;
      } catch (_) {
        return <String, dynamic>{'message': raw};
      }
    }
    return <String, dynamic>{};
  }

  String _extractMessage(Map<String, dynamic> payload, String eventName) {
    final candidates = <dynamic>[
      payload['text'],
      payload['message'],
      payload['mensaje'],
      payload['title'],
      payload['body'],
      payload['status'],
      payload['event'],
    ];

    for (final candidate in candidates) {
      final value = (candidate ?? '').toString().trim();
      if (value.isNotEmpty) return value;
    }

    return '';
  }

  String _extractTitle(Map<String, dynamic> payload) {
    final value = (payload['title'] ?? '').toString().trim();
    if (value.isNotEmpty) return value;
    return 'El Dorado te avisa';
  }

  String? _extractOptional(Map<String, dynamic> payload, List<String> keys) {
    for (final key in keys) {
      final value = (payload[key] ?? '').toString().trim();
      if (value.isNotEmpty) return value;
    }
    return null;
  }
}
