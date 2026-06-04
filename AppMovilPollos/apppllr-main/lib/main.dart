import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import 'screens/my_home_page.dart';
import 'screens/invitado_page.dart';
import 'screens/correo_page.dart';
import 'screens/registro_page.dart';
import 'screens/app_shell.dart';
import 'screens/search_page.dart';
import 'screens/detalles_page_api.dart';
import 'screens/payment_page.dart';
import 'screens/order_confirmed_page.dart';
import 'screens/chat_bot_page.dart';
import 'screens/promo_details_page.dart';
import 'screens/server_config_page.dart';
import 'services/pusher_service.dart';
import 'services/push_notifications_service.dart';
import 'config/runtime_config.dart';

import 'state/cart_controller.dart';
import 'state/orders_controller.dart';
import 'theme/store_theme.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await RuntimeConfig.load();
  await _orders.load();
  await PushNotificationsService.instance.initialize(
    onOpenPromo: (payload) {
      _router.push('/promo', extra: payload);
    },
  );
  // PusherService initialization se hace en AppShell.initState para evitar
  // mensajes de sistema (flutter/lifecycle) antes de que el framework tenga listeners.
  runApp(const MyApp());
}

final _cart = CartController();
final _orders = OrdersController();

final GoRouter _router = GoRouter(
  routes: [

    // Parte externa de la app
    GoRoute(
      path: '/',
      builder: (context, state) => const MyHomePage(title: ''),
    ),

    // Loggeo de cliente
    GoRoute(
      path: '/correo',
      builder: (context, state) => const LoginCorreoPage(),
    ),

    // Registro de cliente
    GoRoute(
      path: '/registro',
      builder: (context, state) => const RegistroPage(),
    ),

    // Visita de persona sin registro
    GoRoute(
      path: '/invitado',
      builder: (context, state) => const InvitadoPage(),
    ),

    // APP PRINCIPAL (Bottom Navigation)
    GoRoute(
      path: '/app',
      builder: (context, state) => const AppShell(),
    ),

    // BUSCAR
    GoRoute(
      path: '/buscar',
      builder: (context, state) => const SearchPage(),
    ),

    // DETALLE
    GoRoute(
      path: '/detalles/:id',
      builder: (context, state) {
        final id = int.parse(state.pathParameters['id']!);
        return DetallesPageApi(productId: id);
      },
    ),

    // PAGO
    GoRoute(
      path: '/pago',
      builder: (context, state) => const PaymentPage(),
    ),

    // CONFIRMACION
    GoRoute(
      path: '/confirmacion',
      builder: (context, state) {
        final extra = state.extra;
        if (extra is Map) {
          final data = extra.cast<String, dynamic>();
          return OrderConfirmedPage(
            trackingCode: data['trackingCode']?.toString(),
            totalPaid: (data['totalPaid'] as num?)?.toDouble(),
            itemsText: data['itemsText']?.toString(),
          );
        }
        return const OrderConfirmedPage();
      },
    ),

    // CHATBOT
    GoRoute(
      path: '/chat',
      builder: (context, state) => const ChatBotPage(),
    ),

    // PROMO DETAILS
    GoRoute(
      path: '/promo',
      builder: (context, state) => PromoDetailsPage.fromExtra(state.extra),
    ),

    GoRoute(
      path: '/config',
      builder: (context, state) => const ServerConfigPage(),
    ),
  ],
);

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return CartScope(
      controller: _cart,
      child: OrdersScope(
        controller: _orders,
        child: MaterialApp.router(
          debugShowCheckedModeBanner: false,
          routerConfig: _router,
          builder: (context, child) {
            final mq = MediaQuery.of(context);

            // Escala tipografía de forma responsiva según el ancho del dispositivo,
            // sin depender de flags al ejecutar. Evita textos gigantes en pantallas
            // pequeñas y mantiene legibilidad en pantallas grandes.
            final shortestSide = mq.size.shortestSide;
            final responsiveFactor = (shortestSide / 390.0).clamp(0.88, 1.08);
            final effectiveScale = (mq.textScaleFactor * responsiveFactor).clamp(0.88, 1.25);

            return MediaQuery(
              data: mq.copyWith(textScaler: TextScaler.linear(effectiveScale)),
              child: child ?? const SizedBox.shrink(),
            );
          },
          theme: StoreTheme.theme(),
        ),
      ),
    );
  }
}
