import 'dart:async';

import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

import '../services/auth_service.dart';
import '../services/delivery_order_service.dart';
import '../services/session_service.dart';
import '../theme/store_theme.dart';

class DeliveryShell extends StatefulWidget {
  const DeliveryShell({super.key});

  @override
  State<DeliveryShell> createState() => _DeliveryShellState();
}

class _DeliveryShellState extends State<DeliveryShell>
    with SingleTickerProviderStateMixin {
  late final TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _logout() async {
    await AuthService().logout();
    if (!mounted) return;
    context.go('/correo');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: StoreTheme.background,
      appBar: AppBar(
        title: const Text(
          'Reparto',
          style: TextStyle(fontWeight: FontWeight.w900),
        ),
        backgroundColor: StoreTheme.orange,
        foregroundColor: StoreTheme.ink,
        actions: [
          IconButton(
            onPressed: _logout,
            icon: const Icon(Icons.logout),
            tooltip: 'Cerrar sesion',
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          labelColor: StoreTheme.ink,
          unselectedLabelColor: StoreTheme.ink.withOpacity(.6),
          indicatorColor: StoreTheme.ink,
          tabs: const [
            Tab(text: 'Disponibles'),
            Tab(text: 'Mis entregas'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: const [
          _DeliveryPoolTab(),
          _DeliveryMineTab(),
        ],
      ),
    );
  }
}

class _DeliveryPoolTab extends StatefulWidget {
  const _DeliveryPoolTab();

  @override
  State<_DeliveryPoolTab> createState() => _DeliveryPoolTabState();
}

class _DeliveryPoolTabState extends State<_DeliveryPoolTab> {
  final _service = DeliveryOrderService();
  final _sessionService = SessionService();
  List<Map<String, dynamic>> _orders = const [];
  bool _loading = true;
  String? _error;
  Timer? _timer;
  final Set<int> _claiming = {};

  @override
  void initState() {
    super.initState();
    _load();
    _timer = Timer.periodic(const Duration(seconds: 15), (_) => _load(silent: true));
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<void> _load({bool silent = false}) async {
    if (!silent) setState(() => _loading = true);
    try {
      final token = await _sessionService.getToken();
      final orders = await _service.pool(token: token);
      if (!mounted) return;
      setState(() {
        _orders = orders;
        _error = null;
      });
    } catch (e) {
      if (!mounted) return;
      if (!silent) setState(() => _error = 'No se pudo cargar la lista.');
    } finally {
      if (mounted && !silent) setState(() => _loading = false);
    }
  }

  Future<void> _claim(Map<String, dynamic> order) async {
    final id = (order['id'] as num).toInt();
    if (_claiming.contains(id)) return;
    setState(() => _claiming.add(id));
    try {
      final token = await _sessionService.getToken();
      await _service.claim(token: token, orderId: id);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Tomaste el pedido ${order['tracking_code'] ?? ''}.')),
      );
      await _load();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString().replaceFirst('Exception: ', ''))),
      );
      await _load();
    } finally {
      if (mounted) setState(() => _claiming.remove(id));
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const Center(child: CircularProgressIndicator());

    return RefreshIndicator(
      onRefresh: _load,
      child: _orders.isEmpty
          ? ListView(
              padding: const EdgeInsets.all(24),
              children: [
                const SizedBox(height: 40),
                Icon(Icons.inventory_2_outlined, size: 48, color: StoreTheme.orange),
                const SizedBox(height: 12),
                Text(
                  _error ?? 'No hay pedidos disponibles por ahora.',
                  textAlign: TextAlign.center,
                  style: const TextStyle(color: StoreTheme.inkSoft),
                ),
              ],
            )
          : ListView.builder(
              padding: const EdgeInsets.all(14),
              itemCount: _orders.length,
              itemBuilder: (context, index) {
                final order = _orders[index];
                final id = (order['id'] as num).toInt();
                return _OrderCard(
                  order: order,
                  actions: [
                    SizedBox(
                      width: double.infinity,
                      child: FilledButton(
                        style: FilledButton.styleFrom(
                          backgroundColor: StoreTheme.orange,
                          foregroundColor: StoreTheme.ink,
                        ),
                        onPressed: _claiming.contains(id) ? null : () => _claim(order),
                        child: Text(_claiming.contains(id) ? 'Tomando...' : 'Tomar pedido'),
                      ),
                    ),
                  ],
                );
              },
            ),
    );
  }
}

class _DeliveryMineTab extends StatefulWidget {
  const _DeliveryMineTab();

  @override
  State<_DeliveryMineTab> createState() => _DeliveryMineTabState();
}

class _DeliveryMineTabState extends State<_DeliveryMineTab> {
  final _service = DeliveryOrderService();
  final _sessionService = SessionService();
  List<Map<String, dynamic>> _orders = const [];
  bool _loading = true;
  final Set<int> _delivering = {};

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load({bool silent = false}) async {
    if (!silent) setState(() => _loading = true);
    try {
      final token = await _sessionService.getToken();
      final orders = await _service.mine(token: token);
      if (!mounted) return;
      setState(() => _orders = orders);
    } catch (_) {
      // Se reintenta en el proximo pull-to-refresh.
    } finally {
      if (mounted && !silent) setState(() => _loading = false);
    }
  }

  Future<void> _markDelivered(Map<String, dynamic> order) async {
    final id = (order['id'] as num).toInt();
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Marcar como entregado'),
        content: Text('Confirma que ya entregaste el pedido ${order['tracking_code'] ?? ''}.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancelar'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Si, entregado'),
          ),
        ],
      ),
    );
    if (confirmed != true) return;

    if (_delivering.contains(id)) return;
    setState(() => _delivering.add(id));
    try {
      final token = await _sessionService.getToken();
      await _service.markDelivered(token: token, orderId: id);
      await _load();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString().replaceFirst('Exception: ', ''))),
      );
    } finally {
      if (mounted) setState(() => _delivering.remove(id));
    }
  }

  Future<void> _openRoute(Map<String, dynamic> order) async {
    final lat = double.tryParse((order['latitude'] ?? '').toString());
    final lng = double.tryParse((order['longitude'] ?? '').toString());
    final address = (order['address'] ?? '').toString().trim();

    final destination = (lat != null && lng != null)
        ? '$lat,$lng'
        : Uri.encodeComponent(address);

    if (destination.isEmpty) return;

    final uri = Uri.parse('https://www.google.com/maps/dir/?api=1&destination=$destination');
    await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  Future<void> _call(Map<String, dynamic> order) async {
    final phone = (order['customer_phone'] ?? '').toString().trim();
    if (phone.isEmpty) return;
    await launchUrl(Uri.parse('tel:$phone'));
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const Center(child: CircularProgressIndicator());

    return RefreshIndicator(
      onRefresh: _load,
      child: _orders.isEmpty
          ? ListView(
              padding: const EdgeInsets.all(24),
              children: [
                const SizedBox(height: 40),
                Icon(Icons.local_shipping_outlined, size: 48, color: StoreTheme.orange),
                const SizedBox(height: 12),
                const Text(
                  'Sin entregas activas. Ve a "Disponibles" para tomar una.',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: StoreTheme.inkSoft),
                ),
              ],
            )
          : ListView.builder(
              padding: const EdgeInsets.all(14),
              itemCount: _orders.length,
              itemBuilder: (context, index) {
                final order = _orders[index];
                final id = (order['id'] as num).toInt();
                return _OrderCard(
                  order: order,
                  actions: [
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: () => _openRoute(order),
                            icon: const Icon(Icons.directions_outlined),
                            label: const Text('Ver ruta'),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: () => _call(order),
                            icon: const Icon(Icons.call_outlined),
                            label: const Text('Llamar'),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    SizedBox(
                      width: double.infinity,
                      child: FilledButton(
                        style: FilledButton.styleFrom(
                          backgroundColor: StoreTheme.orange,
                          foregroundColor: StoreTheme.ink,
                        ),
                        onPressed: _delivering.contains(id) ? null : () => _markDelivered(order),
                        child: Text(_delivering.contains(id) ? 'Guardando...' : 'Marcar entregado'),
                      ),
                    ),
                  ],
                );
              },
            ),
    );
  }
}

class _OrderCard extends StatelessWidget {
  const _OrderCard({required this.order, required this.actions});

  final Map<String, dynamic> order;
  final List<Widget> actions;

  @override
  Widget build(BuildContext context) {
    final tracking = (order['tracking_code'] ?? '').toString();
    final address = (order['address'] ?? '').toString();
    final reference = (order['reference'] ?? '').toString();
    final customerName = (order['customer_name'] ?? '').toString();
    final customerPhone = (order['customer_phone'] ?? '').toString();
    final paymentMethod = (order['payment_method'] ?? '').toString();
    final total = double.tryParse((order['total_amount'] ?? '0').toString()) ?? 0;
    final isCod = paymentMethod == 'cod';

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: StoreTheme.surface,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: StoreTheme.borderSoft),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text(
                '#$tracking',
                style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 15),
              ),
              const Spacer(),
              Text(
                isCod ? 'A cobrar: S/ ${total.toStringAsFixed(2)}' : 'Pagado',
                style: TextStyle(
                  fontWeight: FontWeight.w800,
                  color: isCod ? StoreTheme.orangeDeep : const Color(0xFF1E7F6B),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(address, style: const TextStyle(fontSize: 14)),
          if (reference.isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(top: 2),
              child: Text(
                'Ref: $reference',
                style: const TextStyle(fontSize: 12.5, color: StoreTheme.inkSoft),
              ),
            ),
          const SizedBox(height: 8),
          Text(
            '$customerName · $customerPhone',
            style: const TextStyle(fontSize: 13, color: StoreTheme.inkSoft),
          ),
          const SizedBox(height: 12),
          ...actions,
        ],
      ),
    );
  }
}
