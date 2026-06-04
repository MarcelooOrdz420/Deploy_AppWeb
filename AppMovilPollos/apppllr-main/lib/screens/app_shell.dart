import 'dart:async';

import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../config/api_config.dart';
import '../config/pusher_config.dart';
import '../services/pusher_service.dart';
import '../services/session_service.dart';
import '../state/app_shell_controller.dart';
import '../theme/store_theme.dart';
import 'cart_tab.dart';
import 'home_tab.dart';
import 'orders_tab.dart';
import 'profile_tab.dart';

class AppShell extends StatefulWidget {
  const AppShell({super.key});

  @override
  State<AppShell> createState() => _AppShellState();
}

class _AppShellState extends State<AppShell> {
  int _index = 0;
  StreamSubscription<PusherMessage>? _pusherSubscription;
  String _userRole = 'customer';

  final List<Widget> _pages = const [
    HomeTab(),
    OrdersTab(),
    CartTab(),
    ProfileTab(),
  ];

  @override
  void initState() {
    super.initState();
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
      if (message.name == 'chatbot.reply' || message.name == 'chat.message') return;
      if (message.channel != PusherConfig.notificationsChannel) return;

      final type = (message.data['type'] ?? '').toString().trim().toLowerCase();
      final target = (message.data['target'] ?? '').toString().trim().toLowerCase();
      if (type == 'order_created') {
        final allowAdminNotification = _userRole == 'admin' && (target.isEmpty || target == 'admin' || target == 'all');
        if (!allowAdminNotification) return;
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
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(28),
                      gradient: const LinearGradient(
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                        colors: [Color(0xFFFFFDF9), Color(0xFFFFF3E6)],
                      ),
                      border: Border.all(color: StoreTheme.lineStrong.withOpacity(.85)),
                      boxShadow: const [
                        BoxShadow(
                          color: Color.fromRGBO(52, 17, 0, .2),
                          blurRadius: 32,
                          offset: Offset(0, 16),
                        ),
                      ],
                    ),
                    child: SingleChildScrollView(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Container(
                            width: 74,
                            height: 74,
                            padding: const EdgeInsets.all(10),
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: const Color(0xFFFFE9D7),
                              border: Border.all(color: StoreTheme.lineStrong.withOpacity(.8)),
                            ),
                            child: Image.asset('assets/polloia.png', fit: BoxFit.contain),
                          ),
                          if (imageUrl.isNotEmpty) ...[
                            const SizedBox(height: 14),
                            ClipRRect(
                              borderRadius: BorderRadius.circular(22),
                              child: _promoImage(imageUrl, height: promoImageHeight),
                            ),
                          ],
                          const SizedBox(height: 14),
                          Text(
                            message.title,
                            textAlign: TextAlign.center,
                            style: const TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.w900,
                              color: StoreTheme.ink,
                            ),
                          ),
                          const SizedBox(height: 10),
                          Text(
                            message.message,
                            textAlign: TextAlign.center,
                            style: const TextStyle(
                              color: StoreTheme.inkSoft,
                              height: 1.5,
                            ),
                          ),
                          const SizedBox(height: 18),
                          SizedBox(
                            width: double.infinity,
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
                          const SizedBox(height: 10),
                          TextButton(
                            onPressed: () => Navigator.of(context).pop(),
                            child: const Text('Rechazar'),
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
    final trackingCode = (message.data['tracking_code'] ?? '').toString().trim();

    showDialog<void>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(message.title),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(body.isEmpty ? message.message : body),
            if (trackingCode.isNotEmpty) ...[
              const SizedBox(height: 10),
              Text(
                'Codigo: $trackingCode',
                style: const TextStyle(fontWeight: FontWeight.w700),
              ),
            ],
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cerrar'),
          ),
          FilledButton(
            style: FilledButton.styleFrom(
              backgroundColor: StoreTheme.orange,
              foregroundColor: StoreTheme.ink,
            ),
            onPressed: () {
              Navigator.of(context).pop();
              AppShellController.instance.goTo(1);
              if (!mounted) return;
              setState(() => _index = 1);
            },
            child: const Text('Ver pedidos'),
          ),
        ],
      ),
    );
  }

  void _openOffer(PusherMessage message) {
    // Si el server envía el contenido, abrimos una pantalla de detalle de promo.
    final hasPromoText = (message.data['title'] ?? '').toString().trim().isNotEmpty ||
        (message.data['message'] ?? '').toString().trim().isNotEmpty ||
        (message.data['body'] ?? '').toString().trim().isNotEmpty;

    if (hasPromoText) {
      context.push('/promo', extra: message.data);
      return;
    }

    // Allow server to explicitly control where the CTA lands.
    final route = (message.data['route'] ?? message.data['deep_link'] ?? '').toString().trim();
    if (route.isNotEmpty && route.startsWith('/')) {
      context.push(route);
      return;
    }

    final rawProductId = message.data['product_id'] ?? message.data['productId'] ?? message.data['id'];
    final productId = rawProductId is num ? rawProductId.toInt() : int.tryParse(rawProductId?.toString() ?? '');
    if (productId != null && productId > 0) {
      context.push('/detalles/$productId');
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
                  child: IndexedStack(
                    index: _index,
                    children: _pages,
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.fromLTRB(10, 0, 10, 10),
                  child: DecoratedBox(
                    decoration: BoxDecoration(
                      color: StoreTheme.paper.withOpacity(.92),
                      borderRadius: BorderRadius.circular(24),
                      border: Border.all(color: StoreTheme.lineStrong.withOpacity(.78)),
                    ),
                    child: BottomNavigationBar(
                      currentIndex: _index,
                      onTap: (i) {
                        setState(() => _index = i);
                        AppShellController.instance.goTo(i);
                      },
                      items: const [
                        BottomNavigationBarItem(
                          icon: Icon(Icons.home_outlined),
                          label: 'Inicio',
                        ),
                        BottomNavigationBarItem(
                          icon: Icon(Icons.receipt_long_outlined),
                          label: 'Pedidos',
                        ),
                        BottomNavigationBarItem(
                          icon: Icon(Icons.shopping_cart_outlined),
                          label: 'Carrito',
                        ),
                        BottomNavigationBarItem(
                          icon: Icon(Icons.person_outline),
                          label: 'Perfil',
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
        floatingActionButton: Padding(
          padding: const EdgeInsets.only(bottom: 58),
          child: FloatingActionButton(
            backgroundColor: StoreTheme.orange,
            foregroundColor: StoreTheme.ink,
            elevation: 6,
            onPressed: () => context.push('/chat'),
            child: const Icon(Icons.smart_toy_outlined),
          ),
        ),
      ),
    );
  }
}
