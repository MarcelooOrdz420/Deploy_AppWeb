import 'dart:convert';

import 'package:flutter/services.dart';
import 'package:shared_preferences/shared_preferences.dart';

class RuntimeConfig {
  RuntimeConfig._();

  static const String _assetPath = 'assets/runtime_config.json';
  static const String _prefsApiOriginKey = 'api_origin';

  static bool _loaded = false;
  static Map<String, dynamic> _assetData = const <String, dynamic>{};
  static Map<String, dynamic> _data = const <String, dynamic>{};
  static SharedPreferences? _prefs;

  static bool get isLoaded => _loaded;

  static Future<void> load() async {
    if (_loaded) return;
    _prefs = await SharedPreferences.getInstance();
    try {
      final raw = await rootBundle.loadString(_assetPath);
      final decoded = jsonDecode(raw);
      if (decoded is Map<String, dynamic>) {
        _assetData = Map<String, dynamic>.from(decoded);
        _data = Map<String, dynamic>.from(_assetData);
      } else {
        _assetData = const <String, dynamic>{};
        _data = const <String, dynamic>{};
      }
    } catch (_) {
      _assetData = const <String, dynamic>{};
      _data = const <String, dynamic>{};
    } finally {
      final savedOrigin = (_prefs?.getString(_prefsApiOriginKey) ?? '').trim();
      if (savedOrigin.isNotEmpty) {
        _data = Map<String, dynamic>.from(_data)..['api_origin'] = savedOrigin;
      }
      _loaded = true;
    }
  }

  static String get apiOrigin => _string(_data['api_origin']);
  static String get googleServerClientId => _string(_map(_data['google'])['server_client_id']);

  static Future<void> setApiOrigin(String origin) async {
    final value = origin.trim();
    if (value.isEmpty) return;
    _prefs ??= await SharedPreferences.getInstance();
    await _prefs!.setString(_prefsApiOriginKey, value);
    _data = Map<String, dynamic>.from(_data)..['api_origin'] = value;
  }

  static Future<void> clearApiOrigin() async {
    _prefs ??= await SharedPreferences.getInstance();
    await _prefs!.remove(_prefsApiOriginKey);
    _data = Map<String, dynamic>.from(_assetData);
  }

  static String get pusherAppKey => _string(_map(_data['pusher'])['app_key']);
  static String get pusherCluster => _string(_map(_data['pusher'])['cluster']);
  static bool get pusherUseTls => _bool(_map(_data['pusher'])['use_tls'], fallback: true);
  static String get pusherNotificationsChannel =>
      _string(_map(_data['pusher'])['notifications_channel'], fallback: 'mi-canal');

  static Map<String, dynamic> _map(dynamic value) =>
      value is Map<String, dynamic> ? value : <String, dynamic>{};

  static String _string(dynamic value, {String fallback = ''}) {
    final v = (value ?? '').toString().trim();
    return v.isEmpty ? fallback : v;
  }

  static bool _bool(dynamic value, {required bool fallback}) {
    if (value is bool) return value;
    if (value is String) {
      final v = value.trim().toLowerCase();
      if (v == 'true') return true;
      if (v == 'false') return false;
    }
    if (value is num) return value != 0;
    return fallback;
  }
}
