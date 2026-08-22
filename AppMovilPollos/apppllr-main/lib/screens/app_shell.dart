import 'dart:async';

import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../config/api_config.dart';
import '../config/pusher_config.dart';
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

      final imageUrl = _resolvePromoImage(message.imageUrl);
      showGeneralDialog<void>(
        context: context,
        barrierDismissible: true,
        barrierLabel: 'promo',
        barrierColor: Colors.black54,
        pageBuilder: (context, _, __) {
          final mq = MediaQuery.of(context);
          final dialogWidth = (mq.size.width - 40).clamp(280.0, 420.0);
          final dialogMaxHeight = mq.size.height * .82;
          final promoImageHeight = (dialogWidth * .48).clamp(140.0, 210.0);

          return Center(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Material(
                color: Colors.transparent,
                child: ConstrainedBox(
                  constraints: BoxConstraints(
                    maxWidth: dialogWidth,
                    maxHeight: dialogMaxHeight,
                  ),
                  child: Container(
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(24),
                      color: StoreTheme.paper,
                      border: Border.all(color: const Color(0xFFFFB37A)),
                      boxShadow: const [
                        BoxShadow(
                          color: Color.fromRGBO(255, 111, 31, .22),
                          blurRadius: 40,
                          offset: Offset(0, 20),
                        ),
                      ],
                    ),
                    child: SingleChildScrollView(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          Container(
                            padding: const EdgeInsets.all(16),
                            decoration: const BoxDecoration(
                              color: StoreTheme.orange,
                              borderRadius: BorderRadius.vertical(
                                top: Radius.circular(24),
                              ),
                            ),
                            child: Row(
                              children: [
                                Container(
                                  width: 40,
                                  height: 40,
                                  padding: const EdgeInsets.all(6),
                                  decoration: BoxDecoration(
                                    borderRadius: BorderRadius.circular(12),
                                    color: Colors.white.withOpacity(.22),
                                  ),
                                  child: Image.asset(
                                    'assets/polloia.png',
                                    fit: BoxFit.contain,
                                  ),
                                ),
                                const SizedBox(width: 10),
                                const Expanded(
                                  child: Text(
                                    'PROMO DEL DIA',
                                    style: TextStyle(
                                      color: Colors.white,
                                      fontSize: 11,
                                      letterSpacing: 2,
                                      fontWeight: FontWeight.w900,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          if (imageUrl.isNotEmpty) ...[
                            Padding(
                              padding: const EdgeInsets.fromLTRB(18, 16, 18, 0),
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(16),
                                child: _promoImage(
                                  imageUrl,
                                  height: promoImageHeight,
                                ),
                              ),
                            ),
                          ],
                          Padding(
                            padding: const EdgeInsets.fromLTRB(18, 16, 18, 0),
                            child: Text(
                              message.title,
                              style: const TextStyle(
                                fontSize: 30,
                                height: .98,
                                fontWeight: FontWeight.w900,
                                color: StoreTheme.ink,
                              ),
                            ),
                          ),
                          Padding(
                            padding: const EdgeInsets.fromLTRB(18, 10, 18, 0),
                            child: Text(
                              message.message,
                              style: const TextStyle(
                                color: StoreTheme.inkSoft,
                                height: 1.45,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                          Padding(
                            padding: const EdgeInsets.all(18),
                            child: Row(
                              children: [
                                Expanded(
                                  child: FilledButton(
                                    style: FilledButton.styleFrom(
                                      backgroundColor: StoreTheme.orange,
                                      foregroundColor: StoreTheme.ink,
                                    ),
                                    onPressed: () {
                                      Navigator.of(context).pop();
                                      Future.microtask(() {
                                        if (!mounted) return;
                                        _openOffer(message);
                                      });
                                    },
                                    child: Text(message.actionLabel ?? 'Ver'),
                                  ),
                                ),
                                const SizedBox(width: 10),
                                TextButton(
                                  onPressed: () => Navigator.of(context).pop(),
                                  child: const Text(
                                    'Cerrar',
                                    style: TextStyle(color: StoreTheme.orangeDeep),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ),
          );
        },
      );
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
          child: FloatingActionButton(
            backgroundColor: StoreTheme.orange,
            foregroundColor: StoreTheme.ink,
            elevation: 10,
            onPressed: () => context.push('/chat'),
            child: Padding(
              padding: const EdgeInsets.all(10),
              child: Image.asset('assets/polloia.png', fit: BoxFit.contain),
            ),
          ),
        ),
      ),
    );
  }
}
