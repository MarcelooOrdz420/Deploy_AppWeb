import 'dart:async';

import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../models/producto.dart';
import '../services/auth_service.dart';
import '../services/productos_service.dart';
import '../theme/store_theme.dart';
import '../widgets/producto_image.dart';

class MyHomePage extends StatefulWidget {
  const MyHomePage({super.key, required this.title});
  final String title;

  @override
  State<MyHomePage> createState() => _MyHomePageState();
}

class _MyHomePageState extends State<MyHomePage> {
  bool _showSplash = true;
  late Future<Producto?> _featuredFuture;

  @override
  void initState() {
    super.initState();
    _featuredFuture = _loadFeaturedChicken();
    _boot();
  }

  Future<void> _boot() async {
    final token = await AuthService().getToken();
    if (!mounted) return;

    if (token != null && token.isNotEmpty) {
      context.go('/app');
      return;
    }

    await Future<void>.delayed(const Duration(seconds: 2));
    if (!mounted) return;
    setState(() => _showSplash = false);
  }

  Future<Producto?> _loadFeaturedChicken() async {
    try {
      final products = await ProductosService().listar();
      for (final product in products) {
        if (product.categoria.toLowerCase() == 'pollos') {
          return product;
        }
      }
      return products.isNotEmpty ? products.first : null;
    } catch (_) {
      return null;
    }
  }

  @override
  Widget build(BuildContext context) {
    return StoreBackdrop(
      child: Scaffold(
        body: SafeArea(
          child: FutureBuilder<Producto?>(
            future: _featuredFuture,
            builder: (context, snapshot) {
              final featured = snapshot.data;

              if (_showSplash) {
                return _SplashView(featured: featured);
              }

              return SingleChildScrollView(
                padding: const EdgeInsets.all(14),
                child: Column(
                  children: [
                    StoreFrame(
                      padding: EdgeInsets.zero,
                      child: Padding(
                        padding: const EdgeInsets.all(18),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Container(
                                  width: 58,
                                  height: 58,
                                  padding: const EdgeInsets.all(8),
                                  decoration: BoxDecoration(
                                    borderRadius: BorderRadius.circular(18),
                                    border: Border.all(color: StoreTheme.lineStrong.withOpacity(.88)),
                                    gradient: LinearGradient(
                                      colors: [
                                        Colors.white.withOpacity(.94),
                                        const Color(0xFFFFF1E3),
                                      ],
                                    ),
                                  ),
                                  child: Image.asset('assets/polloia.png'),
                                ),
                                const SizedBox(width: 14),
                                const Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        'Pollo a la Brasa y Parrillas',
                                        style: TextStyle(
                                          fontSize: 11,
                                          fontWeight: FontWeight.w900,
                                          letterSpacing: 2.1,
                                          color: Color(0xFF9B5A2C),
                                        ),
                                      ),
                                      SizedBox(height: 4),
                                      Text(
                                        'Pollos y Parrillas "El Dorado"',
                                        style: TextStyle(
                                          fontSize: 28,
                                          height: 1,
                                          fontWeight: FontWeight.w900,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 18),
                            _WelcomeCard(featured: featured),
                            const SizedBox(height: 18),
                            StoreSurface(
                              padding: const EdgeInsets.all(18),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    'BIENVENIDO',
                                    style: TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w900,
                                      letterSpacing: 2.2,
                                      color: Color(0xFF9B5A2C),
                                    ),
                                  ),
                                  const SizedBox(height: 8),
                                  const Text(
                                    'Ingresa para comprar, seguir tus pedidos y ver el menu con el mismo estilo de la web.',
                                    style: TextStyle(
                                      fontSize: 16,
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
                                        padding: const EdgeInsets.symmetric(vertical: 16),
                                      ),
                                      onPressed: () => context.go('/correo'),
                                      child: const Text('Iniciar sesion'),
                                    ),
                                  ),
                                  const SizedBox(height: 10),
                                  SizedBox(
                                    width: double.infinity,
                                    child: OutlinedButton(
                                      onPressed: () => context.go('/registro'),
                                      child: const Text('Registrarme'),
                                    ),
                                  ),
                                  const SizedBox(height: 10),
                                  Center(
                                    child: TextButton(
                                      onPressed: () => context.go('/invitado'),
                                      child: const Text('Continuar como invitado'),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
        ),
      ),
    );
  }
}

class _SplashView extends StatelessWidget {
  const _SplashView({required this.featured});

  final Producto? featured;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: StoreFrame(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              SizedBox(
                width: 150,
                height: 150,
                child: featured != null
                    ? ClipRRect(
                        borderRadius: BorderRadius.circular(30),
                        child: ProductoImage(producto: featured!),
                      )
                    : Padding(
                        padding: const EdgeInsets.all(18),
                        child: Image.asset('assets/polloia.png'),
                      ),
              ),
              const SizedBox(height: 20),
              const Text(
                'BIENVENIDO',
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w900,
                  letterSpacing: 2.8,
                  color: Color(0xFF9B5A2C),
                ),
              ),
              const SizedBox(height: 8),
              const Text(
                'Pollos y Parrillas "El Dorado"',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 30, height: 1, fontWeight: FontWeight.w900),
              ),
              const SizedBox(height: 10),
              const Text(
                'Preparando el menu para ti...',
                style: TextStyle(color: StoreTheme.inkSoft),
              ),
              const SizedBox(height: 18),
              const SizedBox(
                width: 28,
                height: 28,
                child: CircularProgressIndicator(
                  strokeWidth: 3,
                  color: StoreTheme.orange,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _WelcomeCard extends StatelessWidget {
  const _WelcomeCard({required this.featured});

  final Producto? featured;

  @override
  Widget build(BuildContext context) {
    return StoreSurface(
      padding: EdgeInsets.zero,
      child: ClipRRect(
        borderRadius: BorderRadius.circular(28),
        child: SizedBox(
          height: 260,
          child: Stack(
            fit: StackFit.expand,
            children: [
              if (featured != null)
                ProductoImage(
                  producto: featured!,
                  width: double.infinity,
                  height: double.infinity,
                )
              else
                Image.asset('assets/pollooooo.png', fit: BoxFit.cover),
              DecoratedBox(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                    colors: [
                      Colors.black.withOpacity(.12),
                      Colors.black.withOpacity(.72),
                    ],
                  ),
                ),
              ),
              Positioned(
                left: 18,
                right: 18,
                bottom: 18,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Tu vitrina digital',
                      style: TextStyle(
                        color: Color(0xFFFFF4EB),
                        fontSize: 24,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      featured != null
                          ? 'Hoy te recibimos con ${featured!.name}. Busca, compra y sigue tus pedidos desde aqui.'
                          : 'Busca tus platos favoritos, compra y sigue tus pedidos desde aqui.',
                      style: const TextStyle(
                        color: Color(0xFFFFF4EB),
                        height: 1.45,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
