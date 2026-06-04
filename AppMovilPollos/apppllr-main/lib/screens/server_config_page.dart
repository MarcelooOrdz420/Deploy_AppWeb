import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../config/api_config.dart';
import '../config/runtime_config.dart';
import '../services/api_client.dart';
import '../theme/store_theme.dart';

class ServerConfigPage extends StatefulWidget {
  const ServerConfigPage({super.key});

  @override
  State<ServerConfigPage> createState() => _ServerConfigPageState();
}

class _ServerConfigPageState extends State<ServerConfigPage> {
  final TextEditingController _ctrl = TextEditingController();
  String _status = '';
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    _ctrl.text = ApiConfig.origin;
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  String _normalizeOrigin(String raw) {
    var value = raw.trim();
    if (value.endsWith('/api/v1')) value = value.substring(0, value.length - 7);
    value = value.replaceAll(RegExp(r'/*$'), '');
    return value;
  }

  Future<void> _save() async {
    final origin = _normalizeOrigin(_ctrl.text);
    if (origin.isEmpty) {
      setState(() => _status = 'Ingresa la URL del servidor (API).');
      return;
    }

    final uri = Uri.tryParse(origin);
    if (uri == null || (uri.scheme != 'http' && uri.scheme != 'https') || uri.host.isEmpty) {
      setState(() => _status = 'URL inválida. Ejemplo: http://192.168.1.10:8000');
      return;
    }

    setState(() {
      _saving = true;
      _status = 'Guardando...';
    });

    try {
      await RuntimeConfig.setApiOrigin(origin);
      ApiConfig.setActiveOrigin(origin);
      ApiClient.dio.options.baseUrl = '${origin}${ApiConfig.apiPrefix}';

      // Ping rápido: lista productos (endpoint público)
      await ApiClient.get('/products');

      if (!mounted) return;
      setState(() {
        _saving = false;
        _status = 'Servidor configurado correctamente.';
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _saving = false;
        _status = 'No se pudo conectar al servidor con esa URL.';
      });
    }
  }

  Future<void> _reset() async {
    setState(() {
      _saving = true;
      _status = 'Restaurando...';
    });
    await RuntimeConfig.clearApiOrigin();
    ApiConfig.setActiveOrigin(RuntimeConfig.apiOrigin);
    ApiClient.dio.options.baseUrl = '${ApiConfig.origin}${ApiConfig.apiPrefix}';
    if (!mounted) return;
    setState(() {
      _saving = false;
      _ctrl.text = RuntimeConfig.apiOrigin;
      _status = 'Configuración restaurada. Reinicia la app si aún falla.';
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Configurar servidor'),
        backgroundColor: StoreTheme.orange,
        foregroundColor: StoreTheme.ink,
      ),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'URL de la API',
              style: TextStyle(fontWeight: FontWeight.w900),
            ),
            const SizedBox(height: 6),
            TextField(
              controller: _ctrl,
              keyboardType: TextInputType.url,
              decoration: const InputDecoration(
                hintText: 'http://192.168.1.10:8000',
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 10),
            Text(
              'En celular físico NO uses 127.0.0.1/localhost. Usa la IP de tu PC en WiFi o el dominio del hosting.',
              style: TextStyle(color: StoreTheme.inkSoft, height: 1.4),
            ),
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: FilledButton(
                    style: FilledButton.styleFrom(
                      backgroundColor: StoreTheme.orange,
                      foregroundColor: StoreTheme.ink,
                    ),
                    onPressed: _saving ? null : _save,
                    child: Text(_saving ? '...' : 'Guardar y probar'),
                  ),
                ),
                const SizedBox(width: 10),
                OutlinedButton(
                  onPressed: _saving ? null : _reset,
                  child: const Text('Reset'),
                ),
              ],
            ),
            const SizedBox(height: 12),
            if (_status.isNotEmpty)
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFFFF7F0),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: StoreTheme.lineStrong.withOpacity(.8)),
                ),
                child: Text(_status),
              ),
            const Spacer(),
            TextButton(
              onPressed: () => context.pop(),
              child: const Text('Volver'),
            ),
          ],
        ),
      ),
    );
  }
}
