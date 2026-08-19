import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../services/auth_service.dart';
import '../services/profile_data_service.dart';
import '../services/session_service.dart';
import '../state/app_shell_controller.dart';
import '../state/cart_controller.dart';
import '../state/orders_controller.dart';
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
  bool _marketingEmailsEnabled = true;
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

      try {
        _marketingEmailsEnabled =
            (await _profileData.getPreferences()).marketingEmailsEnabled;
      } catch (_) {}
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
    final cart = CartScope.of(context);
    final orders = OrdersScope.of(context);
    cart.clear();
    await orders.clear();
    await AuthService().logout();
    if (!mounted) return;
    setState(() {
      _logged = false;
      _name = 'Invitado';
      _email = '';
      _addresses = const [];
    });
    AppShellController.instance.goTo(0);
    context.go('/');
  }

  Future<void> _addAddress() async {
    final controller = TextEditingController();
    final value = await _promptValue(
      'Agregar direccion',
      'Ej: Av. Principal 123, Lima',
      controller,
    );
    if (value == null || value.isEmpty) return;
    await _profileData.addAddress(value);
    await _load();
  }

  Future<void> _toggleMarketingEmails(bool value) async {
    await _profileData.updatePreferences(marketingEmailsEnabled: value);
    if (!mounted) return;
    setState(() {
      _marketingEmailsEnabled = value;
    });
  }

  Future<String?> _promptValue(
    String title,
    String hint,
    TextEditingController controller,
  ) {
    return showDialog<String>(
      context: context,
      builder: (context) {
        return AlertDialog(
          backgroundColor: StoreTheme.paper,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(24),
          ),
          title: Text(title),
          content: TextField(
            controller: controller,
            decoration: InputDecoration(hintText: hint),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Cancelar'),
            ),
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
          const Padding(
            padding: EdgeInsets.fromLTRB(4, 4, 4, 16),
            child: Text(
              'Mi cuenta',
              style: TextStyle(fontSize: 32, fontWeight: FontWeight.w900),
            ),
          ),
          StoreSurface(
            child: Row(
              children: [
                CircleAvatar(
                  radius: 38,
                  backgroundColor: const Color(0xFFFFFFFF),
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
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _name,
                        style: const TextStyle(
                          fontSize: 22,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        _email.isEmpty ? 'Completa tu perfil' : _email,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(color: StoreTheme.inkSoft),
                      ),
                    ],
                  ),
                ),
                const Icon(Icons.chevron_right_rounded),
              ],
            ),
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(
                child: _quickAction(Icons.receipt_long_outlined, 'Pedidos', () {
                  AppShellController.instance.goTo(3);
                }),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: _quickAction(
                  Icons.location_on_outlined,
                  'Direcciones',
                  _logged ? _addAddress : () => context.go('/correo'),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: _quickAction(Icons.support_agent_rounded, 'Ayuda', () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text(
                        'Abre POLL-IA para recibir ayuda inmediata.',
                      ),
                    ),
                  );
                }),
              ),
            ],
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
                      leading: const Icon(
                        Icons.location_on_outlined,
                        color: StoreTheme.orangeDeep,
                      ),
                      title: Text(address.address),
                      subtitle: address.label == null
                          ? null
                          : Text(address.label!),
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
            child: SwitchListTile.adaptive(
              contentPadding: EdgeInsets.zero,
              value: _marketingEmailsEnabled,
              onChanged: _logged ? _toggleMarketingEmails : null,
              title: const Text(
                'Correos de promociones y recordatorios',
                style: TextStyle(fontWeight: FontWeight.w900),
              ),
              subtitle: Text(
                _logged
                    ? 'Decide si deseas recibir promociones y recordatorios por correo.'
                    : 'Inicia sesion para gestionar esta preferencia.',
              ),
              activeColor: StoreTheme.orangeDeep,
            ),
          ),
          const SizedBox(height: 14),
          StoreSurface(
            child: Column(
              children: [
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: const Icon(
                    Icons.history,
                    color: StoreTheme.orangeDeep,
                  ),
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
                  style: const TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
              if (actionLabel != null)
                TextButton(onPressed: onAction, child: Text(actionLabel)),
            ],
          ),
          const SizedBox(height: 8),
          ...children,
        ],
      ),
    );
  }

  Widget _quickAction(IconData icon, String label, VoidCallback onTap) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(22),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(22),
        child: Container(
          height: 112,
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 16),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(22),
            border: Border.all(color: StoreTheme.borderSoft),
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, color: StoreTheme.orangeDeep, size: 30),
              const SizedBox(height: 10),
              Text(
                label,
                textAlign: TextAlign.center,
                style: const TextStyle(fontWeight: FontWeight.w800),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
