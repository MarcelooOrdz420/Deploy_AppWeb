import 'dart:async';
import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../services/order_api_service.dart';
import '../services/pusher_service.dart';
import '../services/session_service.dart';
import '../state/orders_controller.dart';
import '../theme/store_theme.dart';
import '../widgets/store_async_state.dart';

class OrdersTab extends StatefulWidget {
  const OrdersTab({super.key});

  @override
  State<OrdersTab> createState() => _OrdersTabState();
}

class _OrdersTabState extends State<OrdersTab> {
  late Future<List<Map<String, dynamic>>> _future;
  StreamSubscription<PusherMessage>? _pusherSubscription;
  Map<String, String> _lastStatuses = const {};

  @override
  void initState() {
    super.initState();
    _future = _loadOrders();
    _listenPusher();
    _loadLastStatuses();
  }

  Future<void> _loadLastStatuses() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = (prefs.getString('order_statuses_v1') ?? '').trim();
    if (raw.isEmpty) return;
    try {
      final decoded = (jsonDecode(raw) as Map).cast<String, dynamic>();
      final map = <String, String>{};
      decoded.forEach((k, v) => map[k.toString()] = (v ?? '').toString());
      _lastStatuses = map;
    } catch (_) {
      _lastStatuses = const {};
    }
  }

  Future<void> _saveLastStatuses(Map<String, String> next) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('order_statuses_v1', jsonEncode(next));
    _lastStatuses = next;
  }

  void _notifyStatusChanges(List<Map<String, dynamic>> serverOrders) {
    if (!mounted) return;
    final next = Map<String, String>.from(_lastStatuses);

    for (final order in serverOrders) {
      final code = (order['tracking_code'] ?? '').toString().trim();
      final status = (order['status'] ?? '').toString().trim();
      if (code.isEmpty) continue;

      final prev = _lastStatuses[code];
      if (prev != null && prev.isNotEmpty && prev != status && status.isNotEmpty) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (!mounted) return;
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Tu pedido $code ahora está: $status'),
              duration: const Duration(seconds: 3),
            ),
          );
        });
      }

      next[code] = status;
    }

    _saveLastStatuses(next);
  }

  void _listenPusher() {
    _pusherSubscription = PusherService.instance.messages.listen((_) {
      if (!mounted) return;
      setState(() {
        _future = _loadOrders();
      });
    });
  }

  Future<List<Map<String, dynamic>>> _loadOrders() async {
    final logged = await SessionService().isLoggedIn();
    if (!logged) return <Map<String, dynamic>>[];

    try {
      final orders = await OrderApiService().myOrders();
      _notifyStatusChanges(orders);
      return orders;
    } catch (_) {
      return <Map<String, dynamic>>[];
    }
  }

  @override
  void dispose() {
    _pusherSubscription?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final localOrders = OrdersScope.of(context).orders;

    return FutureBuilder<List<Map<String, dynamic>>>(
      future: _future,
      builder: (context, snap) {
        if (snap.connectionState == ConnectionState.waiting) {
          return const StoreAsyncState(
            icon: Icons.receipt_long,
            title: 'Cargando pedidos',
            message: 'Estamos revisando tus pedidos en la tienda.',
          );
        }

        final serverOrders = snap.data ?? const <Map<String, dynamic>>[];

        return RefreshIndicator(
          onRefresh: () async {
            setState(() => _future = _loadOrders());
            await _future;
          },
          child: ListView(
            padding: const EdgeInsets.fromLTRB(14, 12, 14, 16),
            children: [
              const StoreSurface(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      '05 Mis pedidos',
                      style: TextStyle(
                        fontSize: 11,
                        letterSpacing: 2.2,
                        color: Color(0xFF9B5A2C),
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    SizedBox(height: 8),
                    Text(
                      'Sigue el estado de tus ordenes y revisa tu boleta.',
                      style: TextStyle(fontSize: 26, fontWeight: FontWeight.w900),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 14),
              if (serverOrders.isEmpty && localOrders.isEmpty)
                const StoreSurface(
                  child: Column(
                    children: [
                      Icon(Icons.inventory_2_outlined, size: 48, color: StoreTheme.orange),
                      SizedBox(height: 12),
                      Text(
                        'Aun no tienes pedidos registrados.',
                        style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900),
                        textAlign: TextAlign.center,
                      ),
                    ],
                  ),
                ),
              ...serverOrders.map(_serverCard),
              ...localOrders.map(_localCard),
            ],
          ),
        );
      },
    );
  }

  Widget _serverCard(Map<String, dynamic> order) {
    final itemList = ((order['items'] as List?) ?? const [])
        .map((e) => (e as Map).cast<String, dynamic>())
        .toList();
    final total = double.tryParse((order['total_amount'] ?? '0').toString()) ?? 0.0;
    final status = (order['status'] ?? '-').toString();

    return StoreSurface(
      margin: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  'Codigo ${order['tracking_code'] ?? '-'}',
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900),
                ),
              ),
              _statusChip(status),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            'Fecha: ${_formatDate(order['created_at'])}',
            style: const TextStyle(color: StoreTheme.inkSoft),
          ),
          const SizedBox(height: 8),
          Text(
            'Pago: ${order['payment_method'] ?? '-'}',
            style: const TextStyle(color: StoreTheme.inkSoft),
          ),
          const SizedBox(height: 12),
          ...itemList.map(
            (item) => Padding(
              padding: const EdgeInsets.only(bottom: 6),
              child: Text(
                '${item['product_name'] ?? '-'} x${item['quantity'] ?? 0}',
                style: const TextStyle(height: 1.4),
              ),
            ),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Text(
                'S/ ${total.toStringAsFixed(2)}',
                style: const TextStyle(
                  color: StoreTheme.orangeDeep,
                  fontSize: 24,
                  fontWeight: FontWeight.w900,
                ),
              ),
              const Spacer(),
              OutlinedButton(
                onPressed: () => _showReceipt(order, itemList, total),
                child: const Text('Ver boleta'),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _localCard(OrderModel order) {
    return StoreSurface(
      margin: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Resumen local #${order.orderId}',
            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900),
          ),
          const SizedBox(height: 8),
          Text(
            'Fecha: ${order.date.day}/${order.date.month}/${order.date.year}',
            style: const TextStyle(color: StoreTheme.inkSoft),
          ),
          const SizedBox(height: 8),
          Text(order.itemsText),
          const SizedBox(height: 12),
          Text(
            'S/ ${order.totalPaid.toStringAsFixed(2)}',
            style: const TextStyle(
              color: StoreTheme.orangeDeep,
              fontSize: 24,
              fontWeight: FontWeight.w900,
            ),
          ),
        ],
      ),
    );
  }

  Widget _statusChip(String status) {
    final normalized = status.toLowerCase();
    final activeColor = normalized == 'delivered'
        ? const Color(0xFF2DBF72)
        : normalized == 'cancelled'
            ? const Color(0xFFE76B3C)
            : StoreTheme.orange;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(999),
        color: const Color(0xFFFFF7F0),
        border: Border.all(color: StoreTheme.lineStrong.withOpacity(.82)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 8,
            height: 8,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: activeColor,
            ),
          ),
          const SizedBox(width: 8),
          Text(
            status,
            style: const TextStyle(
              color: Color(0xFF7E451D),
              fontWeight: FontWeight.w900,
            ),
          ),
        ],
      ),
    );
  }

  String _formatDate(dynamic value) {
    final text = (value ?? '').toString().replaceFirst('T', ' ');
    if (text.length >= 16) return text.substring(0, 16);
    return text;
  }

  void _showReceipt(
    Map<String, dynamic> order,
    List<Map<String, dynamic>> items,
    double total,
  ) {
    showDialog<void>(
      context: context,
      builder: (context) {
        return AlertDialog(
          backgroundColor: StoreTheme.paper,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(26)),
          title: Text('Boleta ${order['tracking_code'] ?? ''}'),
          content: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text('Cliente: ${order['customer_name'] ?? '-'}'),
                Text('Telefono: ${order['customer_phone'] ?? '-'}'),
                Text('Pago: ${order['payment_method'] ?? '-'}'),
                Text('Estado: ${order['status'] ?? '-'}'),
                const SizedBox(height: 12),
                const Text(
                  'Detalle',
                  style: TextStyle(fontWeight: FontWeight.w900),
                ),
                const SizedBox(height: 8),
                ...items.map((item) {
                  final line = double.tryParse((item['line_total'] ?? '0').toString()) ?? 0.0;
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 6),
                    child: Text(
                      '${item['product_name'] ?? '-'} x${item['quantity'] ?? 0} | S/ ${line.toStringAsFixed(2)}',
                    ),
                  );
                }),
                const Divider(height: 24),
                Text(
                  'Total: S/ ${total.toStringAsFixed(2)}',
                  style: const TextStyle(
                    color: StoreTheme.orangeDeep,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Cerrar'),
            ),
          ],
        );
      },
    );
  }
}
