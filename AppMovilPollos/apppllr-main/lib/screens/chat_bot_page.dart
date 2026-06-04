import 'dart:async';

import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../config/pusher_config.dart';
import '../services/chatbot_api_service.dart';
import '../services/pusher_service.dart';
import '../services/session_service.dart';

class ChatBotPage extends StatefulWidget {
  const ChatBotPage({super.key});

  @override
  State<ChatBotPage> createState() => _ChatBotPageState();
}

class _ChatBotPageState extends State<ChatBotPage> {
  static const bool _showDiagnostics =
      bool.fromEnvironment('SHOW_DIAGNOSTICS', defaultValue: false);

  final List<_ChatMessage> _messages = [
    const _ChatMessage(
      role: _ChatRole.bot,
      text:
          'Hola, soy POLL-IA. Puedo ayudarte con dudas sobre productos, pedidos, pagos y delivery. ¿En qué puedo ayudarte hoy?',
      suggestions: ['Productos', 'Pedidos', 'Pagos', 'Delivery'],
    ),
  ];

  final TextEditingController _controller = TextEditingController();
  final ScrollController _scrollController = ScrollController();

  final SessionService _session = SessionService();
  final ChatbotApiService _api = ChatbotApiService();
  final PusherService _pusher = PusherService.instance;

  StreamSubscription<PusherMessage>? _pusherSub;
  String? _chatChannel;
  Timer? _replyFallbackTimer;
  bool _waitingReply = false;

  void _refreshBanner() {
    if (!mounted) return;
    setState(() {});
  }

  @override
  void initState() {
    super.initState();
    _pusher.connectionState.addListener(_refreshBanner);
    _pusher.lastError.addListener(_refreshBanner);
    _pusher.lastEvent.addListener(_refreshBanner);
    _pusher.lastMessageText.addListener(_refreshBanner);
    _pusher.subscribedChannels.addListener(_refreshBanner);
    _initRealtime();
  }

  @override
  void dispose() {
    _pusherSub?.cancel();
    _replyFallbackTimer?.cancel();
    _pusher.connectionState.removeListener(_refreshBanner);
    _pusher.lastError.removeListener(_refreshBanner);
    _pusher.lastEvent.removeListener(_refreshBanner);
    _pusher.lastMessageText.removeListener(_refreshBanner);
    _pusher.subscribedChannels.removeListener(_refreshBanner);
    _controller.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _initRealtime() async {
    try {
      if (!_pusher.isConfigured) {
        print('CHAT: Pusher no configurado (revisa assets/runtime_config.json).');
      }

      final logged = await _session.isLoggedIn();
      final userId = await _session.getUserId();

      if (logged && userId > 0) {
        _chatChannel = PusherConfig.userChannel(userId);
        await _pusher.syncSubscriptions();
      } else {
        final guestId = await _session.getOrCreateGuestSessionId();
        _chatChannel = 'chat-guest.$guestId';
        await _pusher.subscribeToChannel(_chatChannel!);
      }

      if (mounted) setState(() {});
      print('CHAT channel activo: $_chatChannel (logged=$logged userId=$userId)');

      // Canal global de pruebas / notificaciones (por defecto "mi-canal").
      await _pusher.subscribeToChannel(PusherConfig.notificationsChannel);
      if (mounted) setState(() {});

      _pusherSub = _pusher.messages.listen((event) {
        final channel = _chatChannel;
        print('PUSHER EVENT recv: channel=${event.channel} event=${event.name} data=${event.data}');

        if (channel == null) return;
        if (event.channel != channel && event.channel != PusherConfig.notificationsChannel) return;

        if (event.name != 'chatbot.reply' && event.name != 'chat.message') return;

        final text = (event.data['text'] ??
                event.data['message'] ??
                event.data['mensaje'] ??
                event.data['body'] ??
                event.data['title'] ??
                event.message)
            .toString()
            .trim();
        if (text.isEmpty) return;

        _addBotMessage(text);
      });
    } catch (_) {
      // Si Pusher no está configurado o falla, igual podremos responder por HTTP.
    }
  }

  Future<void> _sendText([String? preset]) async {
    final text = (preset ?? _controller.text).trim();
    if (text.isEmpty) return;

    setState(() {
      _messages.add(_ChatMessage(role: _ChatRole.user, text: text));
      _messages.add(const _ChatMessage(
        role: _ChatRole.bot,
        text: 'Escribiendo...',
        isTyping: true,
      ));
      _waitingReply = true;
    });
    _controller.clear();
    _scrollToBottom();

    try {
      final res = await _api.sendMessage(text);
      final reply = (res['reply'] ?? '').toString().trim();
      final serverChannel = (res['channel'] ?? '').toString().trim();

      if (!mounted) return;

      if (serverChannel.isNotEmpty && serverChannel != _chatChannel) {
        _chatChannel = serverChannel;
        await _pusher.subscribeToChannel(serverChannel);
        print('CHAT channel actualizado por API: $_chatChannel');
      }

      if (reply.isEmpty) {
        _addBotMessage('No pude responder en este momento.');
        return;
      }

      // Si no hay Pusher, mostramos la respuesta directa del API.
      if (!_pusher.isConfigured) {
        _addBotMessage(reply);
        return;
      }

      // Si Pusher está configurado pero el evento no llega, hacemos fallback.
      _replyFallbackTimer?.cancel();
      _replyFallbackTimer = Timer(const Duration(seconds: 5), () {
        if (!mounted) return;
        if (!_waitingReply) return;
        _addBotMessage(reply);
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _waitingReply = false;
        _messages.removeWhere((m) => m.isTyping);
        _messages.add(const _ChatMessage(
          role: _ChatRole.bot,
          text: 'No pude enviar tu mensaje. Revisa tu conexión e inténtalo de nuevo.',
        ));
      });
      _scrollToBottom();
    }
  }

  void _addBotMessage(String text) {
    if (!mounted) return;
    _replyFallbackTimer?.cancel();

    setState(() {
      _waitingReply = false;
      _messages.removeWhere((m) => m.isTyping);

      final last = _messages.isNotEmpty ? _messages.last : null;
      if (last != null &&
          last.role == _ChatRole.bot &&
          !last.isTyping &&
          last.text.trim() == text.trim()) {
        return;
      }

      _messages.add(_ChatMessage(role: _ChatRole.bot, text: text));
    });
    _scrollToBottom();
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!_scrollController.hasClients) return;
      _scrollController.animateTo(
        _scrollController.position.maxScrollExtent + 80,
        duration: const Duration(milliseconds: 220),
        curve: Curves.easeOut,
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('POLL-IA'),
        backgroundColor: Colors.orange,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => context.pop(),
        ),
      ),
      body: Column(
        children: [
          if (_showDiagnostics)
            Padding(
              padding: const EdgeInsets.fromLTRB(12, 10, 12, 0),
              child: DecoratedBox(
                decoration: BoxDecoration(
                  color: Colors.black.withOpacity(.04),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _pusher.isConfigured
                            ? 'Realtime: ON (${_pusher.connectionState.value}) • canal: ${_chatChannel ?? '-'} • global: ${PusherConfig.notificationsChannel}'
                            : 'Realtime: OFF (${PusherConfig.notConfiguredReason}) • canal: ${_chatChannel ?? '-'} • global: ${PusherConfig.notificationsChannel}',
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontSize: 12, color: Colors.black54),
                      ),
                      if (_pusher.isConfigured) ...[
                        const SizedBox(height: 6),
                        Text(
                          'Subs: ${_pusher.subscribedChannels.value.join(', ')}',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontSize: 11, color: Colors.black45),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Last: ${_pusher.lastEvent.value ?? '-'}${_pusher.lastError.value != null ? ' • ERR: ${_pusher.lastError.value}' : ''}',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontSize: 11, color: Colors.black45),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Text: ${_pusher.lastMessageText.value ?? '-'}',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontSize: 11, color: Colors.black45),
                        ),
                      ],
                    ],
                  ),
                ),
              ),
            ),
          Expanded(
            child: ListView.builder(
              controller: _scrollController,
              padding: const EdgeInsets.all(12),
              itemCount: _messages.length,
              itemBuilder: (context, index) {
                final msg = _messages[index];
                final isUser = msg.role == _ChatRole.user;

                return Align(
                  alignment: isUser ? Alignment.centerRight : Alignment.centerLeft,
                  child: Container(
                    margin: const EdgeInsets.symmetric(vertical: 6),
                    padding: const EdgeInsets.all(12),
                    constraints: BoxConstraints(
                      maxWidth: MediaQuery.of(context).size.width * 0.82,
                    ),
                    decoration: BoxDecoration(
                      color: isUser ? Colors.orange : Colors.grey.shade200,
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: Column(
                      crossAxisAlignment:
                          isUser ? CrossAxisAlignment.end : CrossAxisAlignment.start,
                      children: [
                        Text(
                          msg.text,
                          style: TextStyle(
                            color: isUser
                                ? Colors.white
                                : (msg.isTyping ? Colors.black45 : Colors.black87),
                            height: 1.35,
                            fontStyle: msg.isTyping ? FontStyle.italic : FontStyle.normal,
                          ),
                        ),
                        if (!isUser && msg.suggestions.isNotEmpty) ...[
                          const SizedBox(height: 10),
                          Wrap(
                            spacing: 8,
                            runSpacing: 8,
                            children: msg.suggestions.map((item) {
                              return ActionChip(
                                label: Text(item),
                                onPressed: () => _sendText(item),
                                backgroundColor: Colors.white,
                                side: const BorderSide(color: Color(0xFFFFC58F)),
                              );
                            }).toList(),
                          ),
                        ],
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
          SafeArea(
            top: false,
            child: Padding(
              padding: const EdgeInsets.fromLTRB(8, 8, 8, 10),
              child: Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _controller,
                      onSubmitted: (_) => _sendText(),
                      decoration: InputDecoration(
                        hintText: 'Escribe tu mensaje...',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.send, color: Colors.orange),
                    onPressed: _sendText,
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

enum _ChatRole { user, bot }

class _ChatMessage {
  final _ChatRole role;
  final String text;
  final List<String> suggestions;
  final bool isTyping;

  const _ChatMessage({
    required this.role,
    required this.text,
    this.suggestions = const [],
    this.isTyping = false,
  });
}
