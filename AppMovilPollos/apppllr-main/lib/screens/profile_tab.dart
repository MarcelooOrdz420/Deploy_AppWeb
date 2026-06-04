import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../services/auth_service.dart';
import '../services/profile_data_service.dart';
import '../services/session_service.dart';
import '../theme/store_theme.dart';

class ProfileTab extends StatefulWidget {
  const ProfileTab({super.key});

  @override
  State<ProfileTab> createState() => _ProfileTabState();
}

class _ProfileTabState extends State<ProfileTab> {
  final _session = SessionService();
  final _profileData = ProfileDataService();
  bool _logged = false;
  String _name = 'Invitado';
  String _email = '';
  List<SavedAddress> _addresses = const [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final logged = await _session.isLoggedIn();
    final name = await _session.getUserName();
    final email = await _session.getUserEmail();
    List<SavedAddress> addresses = const [];

    if (logged) {
      try {
        addresses = await _profileData.getAddresses();
      } catch (_) {
        addresses = const [];
      }
    }

    if (!mounted) return;
    setState(() {
      _logged = logged;
      _name = name;
      _email = email;
      _addresses = addresses;
    });
  }

  Future<void> _logout() async {
    await AuthService().logout();
    if (!mounted) return;
    context.go('/');
  }

  Future<void> _addAddress() async {
    final controller = TextEditingController();
    final value = await _promptValue('Agregar direccion', 'Ej: Av. Principal 123, Lima', controller);
    if (value == null || value.isEmpty) return;
    await _profileData.addAddress(value);
    await _load();
  }

  Future<String?> _promptValue(String title, String hint, TextEditingController controller) {
    return showDialog<String>(
      context: context,
      builder: (context) {
        return AlertDialog(
          backgroundColor: StoreTheme.paper,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
          title: Text(title),
          content: TextField(
            controller: controller,
            decoration: InputDecoration(hintText: hint),
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(context), child: const Text('Cancelar')),
            FilledButton(
              style: FilledButton.styleFrom(
                backgroundColor: StoreTheme.orange,
                foregroundColor: StoreTheme.ink,
              ),
              onPressed: () => Navigator.pop(context, controller.text.trim()),
              child: const Text('Guardar'),
            ),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(14, 12, 14, 16),
        children: [
          StoreSurface(
            child: Column(
              children: [
                CircleAvatar(
                  radius: 38,
                  backgroundColor: const Color(0xFFFFF1E3),
                  child: Text(
                    _name.isNotEmpty ? _name[0].toUpperCase() : 'U',
                    style: const TextStyle(
                      fontSize: 30,
                      fontWeight: FontWeight.w900,
                      color: StoreTheme.orangeDeep,
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                Text(
                  _name,
                  style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900),
                ),
                const SizedBox(height: 4),
                Text(
                  _email.isEmpty ? '-' : _email,
                  style: const TextStyle(color: StoreTheme.inkSoft),
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),
          _section(
            title: 'Direcciones guardadas',
            actionLabel: 'Agregar',
            onAction: _logged ? _addAddress : null,
            children: !_logged
                ? const [
                    Text(
                      'Inicia sesion para guardar direcciones en tu cuenta.',
                      style: TextStyle(color: StoreTheme.inkSoft),
                    ),
                  ]
                : _addresses.isEmpty
                    ? const [
                        Text(
                          'No tienes direcciones guardadas.',
                          style: TextStyle(color: StoreTheme.inkSoft),
                        ),
                      ]
                    : List<Widget>.generate(_addresses.length, (index) {
                        final address = _addresses[index];
                        return ListTile(
                          contentPadding: EdgeInsets.zero,
                          leading: const Icon(Icons.location_on_outlined, color: StoreTheme.orangeDeep),
                          title: Text(address.address),
                          subtitle: address.label == null ? null : Text(address.label!),
                          trailing: IconButton(
                            icon: const Icon(Icons.delete_outline),
                            onPressed: () async {
                              await _profileData.removeAddress(address.id);
                              await _load();
                            },
                          ),
                        );
                      }),
          ),
          const SizedBox(height: 14),
          StoreSurface(
            child: Column(
              children: [
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: const Icon(Icons.history, color: StoreTheme.orangeDeep),
                  title: const Text('Historial de pedidos'),
                  onTap: () => context.go('/app'),
                ),
                const Divider(height: 1),
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: Icon(
                    _logged ? Icons.logout : Icons.login,
                    color: StoreTheme.orangeDeep,
                  ),
                  title: Text(_logged ? 'Cerrar sesion' : 'Iniciar sesion'),
                  onTap: _logged ? _logout : () => context.go('/correo'),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _section({
    required String title,
    required List<Widget> children,
    String? actionLabel,
    VoidCallback? onAction,
  }) {
    return StoreSurface(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  title,
                  style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900),
                ),
              ),
              if (actionLabel != null)
                TextButton(
                  onPressed: onAction,
                  child: Text(actionLabel),
                ),
            ],
          ),
          const SizedBox(height: 8),
          ...children,
        ],
      ),
    );
  }
}

