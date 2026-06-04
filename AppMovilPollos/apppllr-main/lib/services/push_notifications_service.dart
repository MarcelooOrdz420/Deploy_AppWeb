import 'dart:convert';

import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:shared_preferences/shared_preferences.dart';

class PushNotificationsService {
  PushNotificationsService._();

  static final PushNotificationsService instance = PushNotificationsService._();

  final FlutterLocalNotificationsPlugin _local = FlutterLocalNotificationsPlugin();
  bool _initialized = false;

  Future<void> initialize({
    required void Function(Map<String, dynamic> payload) onOpenPromo,
  }) async {
    if (_initialized) return;

    // Firebase en Web requiere opciones (flutterfire configure). En móvil suele bastar.
    try {
      await Firebase.initializeApp();
    } catch (_) {
      // Si no está configurado, no rompemos la app.
      return;
    }

    try {
      await _initLocalNotifications(onOpenPromo: onOpenPromo);

      final messaging = FirebaseMessaging.instance;
      await messaging.requestPermission(alert: true, badge: true, sound: true);

      // Topics para promos globales o solo móviles.
      await messaging.subscribeToTopic('promo_all');
      await messaging.subscribeToTopic('promo_mobile');

      // Topic por usuario (requiere login): avisos de estado de pedidos.
      await syncOrderTopics();

      // Si el usuario abre desde notificación (app en background).
      FirebaseMessaging.onMessageOpenedApp.listen((message) {
        final payload = _promoPayloadFromMessage(message);
        if (payload.isNotEmpty) onOpenPromo(payload);
      });

      // Si el usuario abre con la app totalmente cerrada.
      final initial = await messaging.getInitialMessage();
      if (initial != null) {
        final payload = _promoPayloadFromMessage(initial);
        if (payload.isNotEmpty) {
          Future.microtask(() => onOpenPromo(payload));
        }
      }

      // Si llega en foreground, mostramos una notificación local.
      FirebaseMessaging.onMessage.listen((message) async {
        final payload = _promoPayloadFromMessage(message);
        final title = message.notification?.title ?? (payload['title']?.toString() ?? 'Promoción');
        final body = message.notification?.body ?? (payload['message']?.toString() ?? '');
        await _showLocalNotification(
          title: title,
          body: body,
          payload: payload,
        );
      });

      _initialized = true;
    } catch (_) {
      // No interrumpir app si algo falla.
    }
  }

  Future<void> _initLocalNotifications({
    required void Function(Map<String, dynamic> payload) onOpenPromo,
  }) async {
    const android = AndroidInitializationSettings('@mipmap/ic_launcher');
    const ios = DarwinInitializationSettings();
    const settings = InitializationSettings(android: android, iOS: ios);

    await _local.initialize(
      settings: settings,
      onDidReceiveNotificationResponse: (response) {
        final raw = response.payload ?? '';
        if (raw.trim().isEmpty) return;
        try {
          final decoded = jsonDecode(raw);
          if (decoded is Map<String, dynamic> && decoded.isNotEmpty) {
            onOpenPromo(decoded);
          }
        } catch (_) {}
      },
    );
  }

  Future<void> _showLocalNotification({
    required String title,
    required String body,
    required Map<String, dynamic> payload,
  }) async {
    if (kIsWeb) return;

    const androidDetails = AndroidNotificationDetails(
      'promo_channel',
      'Promociones',
      channelDescription: 'Promociones y ofertas',
      importance: Importance.high,
      priority: Priority.high,
    );
    const details = NotificationDetails(android: androidDetails, iOS: DarwinNotificationDetails());

    await _local.show(
      id: DateTime.now().millisecondsSinceEpoch ~/ 1000,
      title: title,
      body: body,
      notificationDetails: details,
      payload: jsonEncode(payload),
    );
  }

  Map<String, dynamic> _promoPayloadFromMessage(RemoteMessage message) {
    final data = message.data;
    if (data.isEmpty) return <String, dynamic>{};

    // Todos los valores de FCM data vienen como String.
    final route = (data['route'] ?? '').toString().trim();
    if (route != '/promo') {
      // Si quieres manejar otros deep links, lo ampliamos aquí.
      return <String, dynamic>{};
    }

    return <String, dynamic>{
      'title': (data['title'] ?? '').toString(),
      'message': (data['message'] ?? '').toString(),
      'body': (data['body'] ?? '').toString(),
      'image_url': (data['image_url'] ?? '').toString(),
      'cta_label': (data['cta_label'] ?? '').toString(),
      'route': route,
      'target': (data['target'] ?? '').toString(),
    };
  }

  Future<void> syncOrderTopics() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final userId = prefs.getInt('user_id') ?? 0;
      final messaging = FirebaseMessaging.instance;

      final desired = userId > 0 ? 'orders_user_$userId' : '';
      final last = (prefs.getString('fcm_orders_topic') ?? '').trim();

      if (last.isNotEmpty && last != desired) {
        await messaging.unsubscribeFromTopic(last);
      }

      if (desired.isNotEmpty) {
        await messaging.subscribeToTopic(desired);
        await prefs.setString('fcm_orders_topic', desired);
      } else {
        await prefs.remove('fcm_orders_topic');
      }
    } catch (_) {}
  }
}
