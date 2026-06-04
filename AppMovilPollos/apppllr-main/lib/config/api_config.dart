import 'dart:io' show Platform;
import 'package:flutter/foundation.dart' show kIsWeb;

import 'runtime_config.dart';

class ApiConfig {
  static const int port = 8000;
  static const String apiPrefix = '/api/v1';
  static const String _configuredOrigin = String.fromEnvironment('API_ORIGIN');

  static String? _activeOrigin;

  static List<String> get origins {
    final runtimeOrigin = RuntimeConfig.apiOrigin.trim();
    if (runtimeOrigin.isNotEmpty) {
      return _dedupe([runtimeOrigin, ..._defaultOrigins()]);
    }

    if (_configuredOrigin.trim().isNotEmpty) {
      return _dedupe([_configuredOrigin.trim(), ..._defaultOrigins()]);
    }

    return _defaultOrigins();
  }

  static String get origin =>
      (_activeOrigin ?? '').trim().isNotEmpty ? _activeOrigin!.trim() : origins.first;

  static void setActiveOrigin(String origin) {
    final value = origin.trim();
    if (value.isEmpty) return;
    _activeOrigin = value;
  }

  static String originFromBaseUrl(String baseUrl) {
    final value = baseUrl.trim();
    if (value.isEmpty) return origin;
    if (value.endsWith(apiPrefix)) {
      return value.substring(0, value.length - apiPrefix.length);
    }
    final uri = Uri.tryParse(value);
    if (uri == null) return origin;
    return uri
        .replace(path: '', query: null, fragment: null)
        .toString()
        .replaceAll(RegExp(r'/$'), '');
  }

  static List<String> get baseUrls => origins.map((base) => '$base$apiPrefix').toList();

  static String get baseUrl => baseUrls.first;

  static String resolveUrl(String? path) {
    final value = (path ?? '').trim();
    if (value.isEmpty) return '$origin/images/products/default.svg';
    if (value.startsWith('http://') || value.startsWith('https://')) {
      return _normalizeAbsoluteUrl(value);
    }
    if (value.startsWith('/')) return '$origin$value';
    return '$origin/$value';
  }

  static String _normalizeAbsoluteUrl(String raw) {
    final uri = Uri.tryParse(raw);
    if (uri == null) return raw;

    final originUri = Uri.tryParse(origin);
    if (originUri == null) return raw;

    final isLocalHost = uri.host == 'localhost' || uri.host == '127.0.0.1';
    final isLocalOrigin = originUri.host == 'localhost' || originUri.host == '127.0.0.1';
    if (!isLocalHost || !isLocalOrigin) return raw;

    // Muchos endpoints devuelven URLs como `http://localhost/...` sin puerto,
    // pero el backend local suele correr en 8080/8000. Reescribimos a la origin.
    final isDefaultPort =
        (uri.scheme == 'http' && uri.port == 80) ||
            (uri.scheme == 'https' && uri.port == 443) ||
            uri.port == 0;

    if (!isDefaultPort) return raw;

    return uri
        .replace(
          scheme: originUri.scheme,
          host: originUri.host,
          port: originUri.hasPort ? originUri.port : null,
        )
        .toString();
  }

  static List<String> _defaultOrigins() {
    // Soporta `php artisan serve` en 8000 (default) o 8080 (muy común en local).
    final web = [
      'http://127.0.0.1:8000',
      'http://localhost:8000',
      'http://127.0.0.1:8080',
      'http://localhost:8080',
    ];

    if (kIsWeb) return web;

    if (Platform.isAndroid) {
      return [
        'http://10.0.2.2:8000',
        'http://10.0.2.2:8080',
      ];
    }

    return web;
  }

  static List<String> _dedupe(List<String> values) {
    final seen = <String>{};
    final out = <String>[];
    for (final raw in values) {
      final v = raw.trim();
      if (v.isEmpty) continue;
      if (seen.add(v)) out.add(v);
    }
    return out.isEmpty ? values : out;
  }
}

