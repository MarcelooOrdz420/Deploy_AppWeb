import 'dart:async';

import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../config/api_config.dart';
import '../config/pusher_config.dart';
import '../services/order_api_service.dart';
import '../services/pusher_service.dart';
import '../services/session_service.dart';
import '../state/app_shell_controller.dart';
import '../theme/store_theme.dart';
import '../widgets/store_alert_dialog.dart';
import 'cart_tab.dart';
import 'home_tab.dart';
import 'orders_tab.dart';
import 'profile_tab.dart';
import 'search_page.dart';

class AppShell extends StatefulWidget {
  const AppShell({super.key, this.initialIndex = 0});

  final int initialIndex;

  @override
  State<AppShell> createState() => _AppShellState();
}

class _AppShellState extends State<AppShell> {
  late int _index;
  StreamSubscription<PusherMessage>? _pusherSubscription;
  String _userRole = 'customer';

  // Se avisa una sola vez por apertura de la app: si el cliente vuelve a
  // navegar dentro de la misma sesion, ya lo vio.
  static bool _pendingPaymentNoticeShown = false;

  final List<Widget> _pages = const [
    HomeTab(),
    SearchPage(),
    CartTab(),
    OrdersTab(),
    ProfileTab(),
  ];

  @override
  void initState() {
    super.initState();
    _index = widget.initialIndex.clamp(0, 4);
    AppShellController.instance.goTo(_index);
    AppShellController.instance.tabIndex.addListener(_handleTabChange);
    _loadUserRole();
    _initNotifications();
    _checkPendingPayment();
  }

  Future<void> _checkPendingPayment() async {
    if (_pendingPaymentNoticeShown) return;
    final logged = await SessionService().isLoggedIn();
    if (!logged) return;
    List<Map<String, dynamic>> orders;
    try {
      orders = await OrderApiService().myOrders();
    } catch (_) {
      return;
    }
    final pending = orders.where((order) {
      final paymentMethod = (order['payment_method'] ?? '').toString();
      final paymentStatus = (order['payment_status'] ?? '').toString();
      final status = (order['status'] ?? '').toString();
      return paymentMethod == 'izipay' &&
          (paymentStatus == 'pending' || paymentStatus == 'rejected') &&
          status != 'cancelled';
    }).toList();
    if (pending.isEmpty || !mounted) return;
    _pendingPaymentNoticeShown = true;
    final tracking = (pending.first['tracking_code'] ?? '').toString();
    await showStoreAlertDialog(
      context,
      title: 'Tienes un pedido sin completar',
      message:
          'Tu pedido con pago pendiente${tracking.isNotEmpty ? ' ($tracking)' : ''} '
          'quedo guardado en Mis pedidos. Ahi puedes cambiarlo, cancelarlo o '
          'continuar el pago.',
      icon: Icons.receipt_long_rounded,
      buttonLabel: 'Ahora no',
      secondaryLabel: 'Ver Mis pedidos',
      onSecondary: () => AppShellController.instance.goTo(3),
    );
  }

  void _handleTabChange() {
    if (!mounted) return;
    setState(() {
      _index = AppShellController.instance.tabIndex.value;
    });
  }

  Future<void> _loadUserRole() async {
    final role = (await SessionService().getUserRole()).trim().toLowerCase();
    if (!mounted) return;
    setState(() {
      _userRole = role.isEmpty ? 'customer' : role;
    });
  }

  Future<void> _initNotifications() async {
    await PusherService.instance.syncSubscriptions();
    _pusherSubscription = PusherService.instance.messages.listen((message) {
      if (!mounted) return;

      // No mostrar respuestas del chatbot como "notificación" dentro del app shell.
      if (message.name == 'chatbot.reply' || message.name == 'chat.message')
        return;
      if (message.channel != PusherConfig.notificationsChannel) return;

      final type = (message.data['type'] ?? '').toString().trim().toLowerCase();
      final target = (message.data['target'] ?? '')
          .toString()
          .trim()
          .toLowerCase();
      if (type == 'order_created') {
        final allowAdminNotification =
            _userRole == 'admin' &&
            (target.isEmpty || target == 'admin' || target == 'all');
        if (!allowAdminNotification) return;
        _showOrderAlert(message);
        return;
      }

      // Aviso al cliente de que su propio pedido cambio de estado (ej. "en
      // camino"). Sin esta rama caia en el dialogo generico de promociones.
      if (type == 'order_status_updated') {
        _showOrderAlert(message);
        return;
      }

      // Permite que el backend decida si la promo es para mobile/web/all.
      if (target == 'web' || target == 'admin') return;

      // Ya no se interrumpe con un dialogo emergente a quien ya esta usando
      // la app: la promocion vigente se ve siempre en la caja destacada del
      // inicio. Aqui solo se refresca en caliente esa caja.
      AppShellController.instance.refreshPromotions();
    });
  }

  void _showOrderAlert(PusherMessage message) {
    final body = (message.data['body'] ?? message.message).toString().trim();
    final trackingCode = (message.data['tracking_code'] ?? '')
        .toString()
        .trim();
    final resolvedBody = body.isEmpty ? message.message : body;
    final fullMessage = trackingCode.isEmpty
        ? resolvedBody
        : '$resolvedBody\n\nCodigo: $trackingCode';

    showStoreAlertDialog(
      context,
      title: message.title,
      message: fullMessage,
      icon: Icons.receipt_long_rounded,
      buttonLabel: 'Cerrar',
      secondaryLabel: 'Ver pedidos',
      onSecondary: () {
        AppShellController.instance.goTo(3);
        if (!mounted) return;
        setState(() => _index = 3);
      },
    );
  }

  void _openOffer(PusherMessage message) {
    final rawProductId =
        message.data['product_id'] ??
        message.data['productId'] ??
        message.data['id'];
    final productId = rawProductId is num
        ? rawProductId.toInt()
        : int.tryParse(rawProductId?.toString() ?? '');

    // Si la promo trae precio con descuento, siempre prioriza la pantalla de
    // promo (con precio y boton de compra) sobre el detalle normal del
    // platillo: de lo contrario el cliente terminaba pagando el precio
    // completo aunque la promo mostrara un descuento.
    final hasPromoPrice = (message.data['promo_price'] ?? '')
        .toString()
        .trim()
        .isNotEmpty;
    if (hasPromoPrice) {
      context.push('/promo', extra: message.data);
      return;
    }

    if (productId != null && productId > 0) {
      context.push('/detalles/$productId');
      return;
    }

    // Si el server envía el contenido, abrimos una pantalla de detalle de promo.
    final hasPromoText =
        (message.data['title'] ?? '').toString().trim().isNotEmpty ||
        (message.data['message'] ?? '').toString().trim().isNotEmpty ||
        (message.data['body'] ?? '').toString().trim().isNotEmpty;

    if (hasPromoText) {
      context.push('/promo', extra: message.data);
      return;
    }

    // Allow server to explicitly control where the CTA lands.
    final route = (message.data['route'] ?? message.data['deep_link'] ?? '')
        .toString()
        .trim();
    if (route.isNotEmpty && route.startsWith('/')) {
      context.push(route);
      return;
    }

    // Fallback: send them to search so they see products immediately.
    context.push('/buscar');
  }

  String _resolvePromoImage(String? raw) {
    final value = (raw ?? '').trim();
    if (value.isEmpty) {
      return ApiConfig.resolveUrl('/images/products/pollos/pollo_familiar.jpg');
    }
    return ApiConfig.resolveUrl(value);
  }

  Widget _promoImage(String imageUrl, {required double height}) {
    return Image.network(
      imageUrl,
      width: double.infinity,
      height: height,
      fit: BoxFit.cover,
      webHtmlElementStrategy: WebHtmlElementStrategy.prefer,
      errorBuilder: (_, __, ___) => Image.network(
        ApiConfig.resolveUrl('/images/products/pollos/pollo_familiar.jpg'),
        width: double.infinity,
        height: height,
        fit: BoxFit.cover,
        webHtmlElementStrategy: WebHtmlElementStrategy.prefer,
        errorBuilder: (_, __, ___) => Image.asset(
          'assets/pollooooo.png',
          width: double.infinity,
          height: height,
          fit: BoxFit.cover,
        ),
      ),
    );
  }

  @override
  void dispose() {
    AppShellController.instance.tabIndex.removeListener(_handleTabChange);
    _pusherSubscription?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return StoreBackdrop(
      child: Scaffold(
        body: SafeArea(
          child: StoreFrame(
            child: Column(
              children: [
                Expanded(
                  child: IndexedStack(index: _index, children: _pages),
                ),
                Container(
                  margin: const EdgeInsets.fromLTRB(14, 6, 14, 12),
                  clipBehavior: Clip.antiAlias,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(34),
                    color: Colors.white,
                    boxShadow: const [
                      BoxShadow(
                        color: Color.fromRGBO(25, 22, 20, .13),
                        blurRadius: 34,
                        offset: Offset(0, 14),
                      ),
                    ],
                  ),
                  child: NavigationBar(
                    selectedIndex: _index,
                    onDestinationSelected: (i) {
                      setState(() => _index = i);
                      AppShellController.instance.goTo(i);
                    },
                    destinations: const [
                      NavigationDestination(
                        icon: Icon(Icons.home_outlined),
                        selectedIcon: Icon(Icons.home_rounded),
                        label: 'Inicio',
                      ),
                      NavigationDestination(
                        icon: Icon(Icons.search_outlined),
                        selectedIcon: Icon(Icons.search_rounded),
                        label: 'Buscar',
                      ),
                      NavigationDestination(
                        icon: Icon(Icons.shopping_bag_outlined),
                        selectedIcon: Icon(Icons.shopping_bag_rounded),
                        label: 'Carrito',
                      ),
                      NavigationDestination(
                        icon: Icon(Icons.receipt_long_outlined),
                        selectedIcon: Icon(Icons.receipt_long_rounded),
                        label: 'Pedidos',
                      ),
                      NavigationDestination(
                        icon: Icon(Icons.person_outline),
                        selectedIcon: Icon(Icons.person_rounded),
                        label: 'Perfil',
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
        floatingActionButton: Padding(
          padding: const EdgeInsets.only(bottom: 70),
          // Icono propio del chatbot POLL-IA, distinto del logo de la marca
          // (assets/pollia.webp), para no confundirlos.
          child: FloatingActionButton(
            backgroundColor: StoreTheme.orange,
            foregroundColor: Colors.white,
            elevation: 10,
            onPressed: () => context.push('/chat'),
            child: const Icon(Icons.support_agent_rounded, size: 28),
          ),
        ),
      ),
    );
  }
}
