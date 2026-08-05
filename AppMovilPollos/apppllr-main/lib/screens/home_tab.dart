import 'dart:async';

import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../models/producto.dart';
import '../services/productos_service.dart';
import '../services/session_service.dart';
import '../state/app_shell_controller.dart';
import '../state/cart_controller.dart';
import '../theme/store_theme.dart';
import '../widgets/producto_image.dart';
import '../widgets/store_async_state.dart';

class HomeTab extends StatefulWidget {
  const HomeTab({super.key});

  @override
  State<HomeTab> createState() => _HomeTabState();
}

class _HomeTabState extends State<HomeTab> with WidgetsBindingObserver {
  late Future<List<Producto>> _future;
  final TextEditingController _searchCtrl = TextEditingController();
  final TextEditingController _maxPriceCtrl = TextEditingController();
  final PageController _heroController = PageController();
  Timer? _heroTimer;
  int _heroPage = 0;

  String _userName = 'Invitado';
  String _selectedCategory = '';
  bool _hasSelection = false;
  bool _logged = false;

  @override
  void initState() {
    super.initState();
    _future = ProductosService().listar();
    _loadSession();
    WidgetsBinding.instance.addObserver(this);
    _startHeroTimer();
  }

  Future<void> _loadSession() async {
    final name = await SessionService().getUserName();
    final logged = await SessionService().isLoggedIn();
    if (!mounted) return;
    setState(() {
      _userName = name;
      _logged = logged;
    });
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _heroTimer?.cancel();
    _heroController.dispose();
    _searchCtrl.dispose();
    _maxPriceCtrl.dispose();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      _startHeroTimer();
    } else {
      _heroTimer?.cancel();
    }
  }

  void _startHeroTimer() {
    _heroTimer?.cancel();
    _heroTimer = Timer.periodic(const Duration(seconds: 5), (_) {
      if (!mounted || !_heroController.hasClients) return;
      final next = (_heroPage + 1) % 3;
      _heroController.animateToPage(
        next,
        duration: const Duration(milliseconds: 420),
        curve: Curves.easeOutCubic,
      );
    });
  }

  List<Producto> _filteredProducts(List<Producto> products) {
    final query = _normalize(_searchCtrl.text);
    final maxPrice = double.tryParse(_maxPriceCtrl.text.trim());

    return products.where((product) {
      final matchesName = query.isEmpty ||
          _normalize(product.name).contains(query) ||
          _normalize(product.categoria).contains(query);
      final matchesCategory =
          _selectedCategory.isEmpty || _normalizeCategory(product.categoria) == _selectedCategory;
      final matchesPrice = maxPrice == null || product.price <= maxPrice;
      return matchesName && matchesCategory && matchesPrice;
    }).toList();
  }

  String _normalize(String value) {
    const accented = 'áéíóúüñÁÉÍÓÚÜÑ';
    const plain = 'aeiouunAEIOUUN';
    var result = value.trim().toLowerCase();
    for (var index = 0; index < accented.length; index++) {
      result = result.replaceAll(accented[index], plain[index]);
    }
    return result.replaceAll(RegExp(r'\s+'), ' ');
  }

  String _normalizeCategory(String value) {
    final normalized = _normalize(value);
    if (normalized.contains('pollo')) return 'pollos';
    if (normalized.contains('parrilla') || normalized.contains('anticucho')) return 'parrillas';
    if (normalized.contains('bebida') || normalized.contains('gaseosa')) return 'bebidas';
    return normalized;
  }

  Future<void> _addToCart(BuildContext context, Producto product) async {
    final logged = await SessionService().isLoggedIn();
    if (!logged) {
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Debes iniciar sesion para comprar.')),
      );
      context.go('/correo');
      return;
    }

    final cart = CartScope.of(context);
    cart.add(product);
    if (!context.mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('${product.name} agregado al carrito')),
    );
  }

  void _showProductSheet(Producto product) {
    showDialog<void>(
      context: context,
      barrierDismissible: true,
      builder: (dialogContext) {
        final size = MediaQuery.of(dialogContext).size;
        return Dialog(
          insetPadding: const EdgeInsets.all(14),
          backgroundColor: Colors.transparent,
          child: ConstrainedBox(
            constraints: BoxConstraints(
              maxWidth: 520,
              maxHeight: size.height * .82,
            ),
            child: StoreSurface(
              child: Stack(
                children: [
                  Padding(
                    padding: const EdgeInsets.all(14),
                    child: SingleChildScrollView(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          ClipRRect(
                            borderRadius: BorderRadius.circular(22),
                            child: ProductoImage(
                              producto: product,
                              width: double.infinity,
                              height: 190,
                              fit: BoxFit.contain,
                            ),
                          ),
                          const SizedBox(height: 14),
                          Text(
                            product.name,
                            style: const TextStyle(
                              fontSize: 22,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            product.description.isEmpty ? 'Sin descripcion.' : product.description,
                            style: const TextStyle(
                              color: StoreTheme.inkSoft,
                              height: 1.5,
                            ),
                          ),
                          const SizedBox(height: 12),
                          Row(
                            children: [
                              _pill(product.categoria),
                              const Spacer(),
                              Text(
                                'S/ ${product.price.toStringAsFixed(2)}',
                                style: const TextStyle(
                                  color: StoreTheme.orangeDeep,
                                  fontSize: 26,
                                  fontWeight: FontWeight.w900,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 16),
                          SizedBox(
                            width: double.infinity,
                            child: FilledButton(
                              style: FilledButton.styleFrom(
                                backgroundColor: StoreTheme.orange,
                                foregroundColor: StoreTheme.ink,
                                padding: const EdgeInsets.symmetric(vertical: 14),
                              ),
                              onPressed: () async {
                                await _addToCart(dialogContext, product);
                                if (!dialogContext.mounted) return;
                                Navigator.pop(dialogContext);
                              },
                              child: const Text('Agregar al carrito'),
                            ),
                          ),
                          const SizedBox(height: 10),
                          SizedBox(
                            width: double.infinity,
                            child: OutlinedButton(
                              onPressed: () => Navigator.pop(dialogContext),
                              child: const Text('Cerrar'),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  Positioned(
                    right: 4,
                    top: 4,
                    child: IconButton(
                      tooltip: 'Cerrar',
                      onPressed: () => Navigator.pop(dialogContext),
                      icon: const Icon(Icons.close),
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<List<Producto>>(
      future: _future,
      builder: (context, snap) {
        if (snap.connectionState != ConnectionState.done) {
          return const StoreAsyncState(
            icon: Icons.restaurant_menu,
            title: 'Cargando menu',
            message: 'Estamos preparando la vitrina para mostrarte el catalogo.',
          );
        }

        if (snap.hasError) {
          return StoreAsyncState(
            icon: Icons.wifi_off_rounded,
            title: 'No se pudo cargar el menu',
            message: '${snap.error}',
            actionLabel: 'Configurar servidor',
            onAction: () => context.go('/config'),
          );
        }

        final products = snap.data ?? const <Producto>[];
        final filtered = _filteredProducts(products);
        final pollos = products.where((item) => _normalizeCategory(item.categoria) == 'pollos').toList();
        final bebidas = products.where((item) => _normalizeCategory(item.categoria) == 'bebidas').toList();
        final parrillas = products.where((item) => _normalizeCategory(item.categoria) == 'parrillas').toList();

        return RefreshIndicator(
          onRefresh: () async {
            setState(() {
              _future = ProductosService().listar();
            });
            await _future;
          },
          child: ListView(
            padding: const EdgeInsets.fromLTRB(14, 12, 14, 16),
            children: [
              _buildTopBar(context),
              const SizedBox(height: 16),
              _buildHeroCarousel(
                pollos: pollos,
                bebidas: bebidas,
                parrillas: parrillas,
              ),
              const SizedBox(height: 16),
              _buildFilterSection(filtered.length),
              const SizedBox(height: 16),
              if (!_hasSelection)
                const StoreSurface(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Elige una categoria para ver los productos.',
                        style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900),
                      ),
                      SizedBox(height: 8),
                      Text(
                        'Selecciona Pollos, Parrillas, Bebidas o Todas.',
                        style: TextStyle(color: StoreTheme.inkSoft, height: 1.5),
                      ),
                    ],
                  ),
                )
              else if (filtered.isEmpty)
                const StoreAsyncState(
                  icon: Icons.search_off_rounded,
                  title: 'No se encontraron productos',
                  message: 'Cambia o limpia los filtros activos.',
                )
              else
                LayoutBuilder(
                  builder: (context, constraints) {
                    final isWide = constraints.maxWidth >= 720;
                    final isNarrow = constraints.maxWidth < 420;
                    return GridView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                        crossAxisCount: isWide ? 3 : (isNarrow ? 1 : 2),
                        crossAxisSpacing: 14,
                        mainAxisSpacing: 14,
                        mainAxisExtent: isWide ? 400 : (isNarrow ? 420 : 440),
                      ),
                      itemCount: filtered.length,
                      itemBuilder: (context, index) {
                        final product = filtered[index];
                        return _ProductCard(
                          product: product,
                          onOpen: () => _showProductSheet(product),
                          onAdd: () => _addToCart(context, product),
                        );
                      },
                    );
                  },
                ),
              if (!_logged)
                const Padding(
                  padding: EdgeInsets.only(top: 14),
                  child: Text(
                    'Puedes explorar el menu, pero necesitas iniciar sesion para comprar.',
                    textAlign: TextAlign.center,
                    style: TextStyle(color: StoreTheme.inkSoft),
                  ),
                ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildTopBar(BuildContext context) {
    final cartCount = CartScope.of(context).items.fold<int>(0, (sum, item) => sum + item.qty);

    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: StoreTheme.lineStrong.withOpacity(.52)),
        gradient: const LinearGradient(
          begin: Alignment.centerLeft,
          end: Alignment.centerRight,
          colors: [
            Color(0xFF000000),
            Color(0xFF000000),
            Color(0xFF17100A),
            Color(0xFFFFBF00),
          ],
          stops: [0, .62, .86, 1],
        ),
        boxShadow: const [
          BoxShadow(
            color: Color.fromRGBO(52, 17, 0, .12),
            blurRadius: 26,
            offset: Offset(0, 14),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
            decoration: const BoxDecoration(
              color: Color(0xFFFFC20E),
              borderRadius: BorderRadius.vertical(top: Radius.circular(14)),
            ),
            child: const Text(
              'Horario de atencion · Lun-Vie 12 pm a 8 pm · Sab 11 am a 9 pm · Dom 11 am a 7 pm',
              textAlign: TextAlign.center,
              style: TextStyle(
                color: Color(0xFF220A00),
                fontSize: 11,
                fontWeight: FontWeight.w900,
                letterSpacing: .5,
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 14, 16, 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
          Row(
            children: [
              Container(
                width: 48,
                height: 48,
                padding: const EdgeInsets.all(7),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: StoreTheme.lineStrong.withOpacity(.9)),
                  gradient: LinearGradient(
                    colors: [
                      Colors.white.withOpacity(.95),
                      const Color(0xFFFFF1E3),
                    ],
                  ),
                ),
                child: Image.asset('assets/polloia.png'),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Pollo a la Brasa y Parrillas',
                      style: TextStyle(
                        fontSize: 11,
                        letterSpacing: 2.2,
                        color: Color(0xFFFFC20E),
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'Pollos y Parrillas "El Dorado"',
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: 22,
                        fontStyle: FontStyle.italic,
                        fontWeight: FontWeight.w900,
                        color: Color(0xFFFFF8ED),
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      'Hola, $_userName',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(color: Color(0xFFFFE7B4)),
                    ),
                  ],
                ),
              ),
              Stack(
                clipBehavior: Clip.none,
                children: [
                  IconButton(
                    onPressed: () {
                      AppShellController.instance.goTo(2);
                      context.go('/app');
                    },
                    icon: const Icon(Icons.shopping_cart_outlined),
                    style: IconButton.styleFrom(
                      backgroundColor: const Color(0xFF17110D),
                      foregroundColor: Colors.white,
                    ),
                  ),
                  if (cartCount > 0)
                    Positioned(
                      top: -4,
                      right: -2,
                      child: CircleAvatar(
                        radius: 10,
                        backgroundColor: StoreTheme.orangeDeep,
                        child: Text(
                          '$cartCount',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 10,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 14),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            decoration: BoxDecoration(
              color: Color(0xFF000000),
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: Colors.white.withOpacity(.35)),
            ),
            child: const Row(
              children: [
                Icon(Icons.local_fire_department_rounded, color: Color(0xFFFFC20E)),
                SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'Menu visual, rapido y listo para pedir.',
                    style: TextStyle(
                      color: Color(0xFFFFF8ED),
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                ),
              ],
            ),
          ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeroSection({
    required List<Producto> pollos,
    required List<Producto> bebidas,
  }) {
    final primary = pollos.isNotEmpty ? pollos.first : null;
    final secondary = pollos.length > 1 ? pollos[1] : primary;
    final drinks = bebidas.isNotEmpty ? bebidas.first : secondary;

    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(14),
        gradient: const LinearGradient(
          colors: [
            Color(0xFF000000),
            Color(0xFF090604),
            Color(0xFF2A170B),
            Color(0xFFFFBF00),
          ],
          stops: [0, .52, .82, 1],
          begin: Alignment.centerLeft,
          end: Alignment.centerRight,
        ),
        boxShadow: const [
          BoxShadow(
            color: Color.fromRGBO(52, 17, 0, .18),
            blurRadius: 28,
            offset: Offset(0, 16),
          ),
        ],
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Las mejores promos',
            style: TextStyle(
              fontSize: 24,
              height: 1,
              letterSpacing: -.6,
              fontStyle: FontStyle.italic,
              color: Color(0xFFFFBF00),
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'APP\nEL\nDORADO',
            style: TextStyle(
              fontSize: 56,
              height: .78,
              fontWeight: FontWeight.w900,
              color: Colors.white,
              letterSpacing: -2.4,
            ),
          ),
          const SizedBox(height: 10),
          const Text(
            'Descarga, compra y sigue tus pedidos con una experiencia directa, oscura y dorada.',
            style: TextStyle(color: Color(0xFFEFE7DA), height: 1.5, fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 14),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              _pill('Pollos'),
              _pill('Parrillas'),
              _pill('Bebidas'),
            ],
          ),
          const SizedBox(height: 16),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
            decoration: BoxDecoration(
              color: const Color(0xFFFFBF00),
              borderRadius: BorderRadius.circular(4),
              boxShadow: const [
                BoxShadow(
                  color: Color.fromRGBO(0, 0, 0, .28),
                  blurRadius: 18,
                  offset: Offset(0, 10),
                ),
              ],
            ),
            child: const Text(
              'DELIVERY 500 8800',
              textAlign: TextAlign.center,
              style: TextStyle(
                color: Color(0xFFB40020),
                fontSize: 26,
                fontWeight: FontWeight.w900,
                letterSpacing: 1.2,
              ),
            ),
          ),
          const SizedBox(height: 16),
          LayoutBuilder(
            builder: (context, constraints) {
              final stacked = constraints.maxWidth < 380;
              if (stacked) {
                return Column(
                  children: [
                    SizedBox(
                      height: 260,
                      child: _HeroCard(
                        title: primary?.name ?? 'Brasa protagonista',
                        subtitle: primary?.description.isNotEmpty == true
                            ? primary!.description
                            : 'Textura crocante, porcion potente y compra rapida.',
                        product: primary,
                      ),
                    ),
                    const SizedBox(height: 12),
                    SizedBox(
                      height: 170,
                      child: _HeroCard(
                        title: secondary?.name ?? 'Combos',
                        subtitle: secondary?.description.isNotEmpty == true
                            ? secondary!.description
                            : 'Listos para compartir.',
                        product: secondary,
                      ),
                    ),
                    const SizedBox(height: 12),
                    SizedBox(
                      height: 170,
                      child: _HeroCard(
                        title: drinks?.name ?? 'Bebidas',
                        subtitle: drinks?.description.isNotEmpty == true
                            ? drinks!.description
                            : 'El cierre ideal de tu pedido.',
                        product: drinks,
                      ),
                    ),
                  ],
                );
              }

              return Row(
                children: [
                  Expanded(
                    flex: 11,
                    child: SizedBox(
                      height: 350,
                      child: _HeroCard(
                        title: primary?.name ?? 'Brasa protagonista',
                        subtitle: primary?.description.isNotEmpty == true
                            ? primary!.description
                            : 'Textura crocante, porcion potente y compra rapida.',
                        product: primary,
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    flex: 9,
                    child: SizedBox(
                      height: 350,
                      child: Column(
                        children: [
                          Expanded(
                            child: _HeroCard(
                              title: secondary?.name ?? 'Combos',
                              subtitle: secondary?.description.isNotEmpty == true
                                  ? secondary!.description
                                  : 'Listos para compartir.',
                              product: secondary,
                            ),
                          ),
                          const SizedBox(height: 12),
                          Expanded(
                            child: _HeroCard(
                              title: drinks?.name ?? 'Bebidas',
                              subtitle: drinks?.description.isNotEmpty == true
                                  ? drinks!.description
                                  : 'El cierre ideal de tu pedido.',
                              product: drinks,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildHeroCarousel({
    required List<Producto> pollos,
    required List<Producto> bebidas,
    required List<Producto> parrillas,
  }) {
    final products = <Producto?>[
      pollos.isNotEmpty ? pollos.first : null,
      bebidas.isNotEmpty ? bebidas.first : null,
      parrillas.isNotEmpty ? parrillas.first : null,
    ];
    const fallbackTitles = ['Pollos', 'Bebidas', 'Parrillas'];
    const fallbackSubtitles = [
      'Pollo dorado, crocante y listo para pedir.',
      'Bebidas frias para acompañar tu pedido.',
      'Carnes y parrillas preparadas al momento.',
    ];

    return Semantics(
      label: 'Promociones de Pollos y Parrillas El Dorado',
      child: StoreSurface(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Padding(
              padding: EdgeInsets.fromLTRB(4, 2, 4, 12),
              child: Text('Promociones destacadas', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800)),
            ),
            SizedBox(
              height: 330,
              child: PageView.builder(
                controller: _heroController,
                itemCount: products.length,
                onPageChanged: (value) => setState(() => _heroPage = value),
                itemBuilder: (context, index) {
                  final product = products[index];
                  return Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 2),
                    child: _HeroCard(
                      title: fallbackTitles[index],
                      subtitle: product?.description.isNotEmpty == true
                          ? product!.description
                          : fallbackSubtitles[index],
                      product: product,
                      fallbackAsset: const [
                        'assets/pollo_entero.png',
                        'assets/coca_cola.png',
                        'assets/parrillada_mixta.jpg',
                      ][index],
                    ),
                  );
                },
              ),
            ),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: List.generate(products.length, (index) {
                final selected = index == _heroPage;
                return Semantics(
                  button: true,
                  selected: selected,
                  label: 'Mostrar promoción ${index + 1}',
                  child: GestureDetector(
                    onTap: () => _heroController.animateToPage(
                      index,
                      duration: const Duration(milliseconds: 320),
                      curve: Curves.easeOutCubic,
                    ),
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 220),
                      width: selected ? 24 : 9,
                      height: 9,
                      margin: const EdgeInsets.symmetric(horizontal: 4),
                      decoration: BoxDecoration(
                        color: selected ? StoreTheme.orange : StoreTheme.border,
                        borderRadius: BorderRadius.circular(999),
                      ),
                    ),
                  ),
                );
              }),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFilterSection(int resultsCount) {
    return StoreSurface(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Busqueda guiada',
                      style: TextStyle(
                        fontSize: 11,
                        letterSpacing: 2.2,
                        color: Color(0xFF9B5A2C),
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    SizedBox(height: 8),
                    Text(
                      'Filtra por antojo, categoria o presupuesto.',
                      style: TextStyle(fontSize: 24, fontWeight: FontWeight.w900),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                decoration: BoxDecoration(
                  color: const Color(0xFFFFF7F0),
                  borderRadius: BorderRadius.circular(999),
                  border: Border.all(color: StoreTheme.lineStrong.withOpacity(.82)),
                ),
                child: Text(
                  !_hasSelection
                      ? 'Elige una categoria'
                      : resultsCount == 1
                          ? '1 producto encontrado'
                          : '$resultsCount productos encontrados',
                  style: const TextStyle(
                    color: Color(0xFF82471F),
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          StorePanel(
            child: Column(
              children: [
                TextField(
                  controller: _searchCtrl,
                  onChanged: (_) => setState(() => _hasSelection = true),
                  decoration: const InputDecoration(
                    labelText: 'Buscar por nombre',
                    hintText: 'Ej: pollo, parrilla, chicha...',
                    prefixIcon: Icon(Icons.search),
                  ),
                ),
                const SizedBox(height: 12),
                DropdownButtonFormField<String>(
                  value: _selectedCategory.isEmpty ? null : _selectedCategory,
                  decoration: const InputDecoration(
                    labelText: 'Categoria',
                    prefixIcon: Icon(Icons.tune),
                  ),
                  items: const [
                    DropdownMenuItem(value: '', child: Text('Todas')),
                    DropdownMenuItem(value: 'pollos', child: Text('Pollos')),
                    DropdownMenuItem(value: 'parrillas', child: Text('Parrillas')),
                    DropdownMenuItem(value: 'bebidas', child: Text('Bebidas')),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedCategory = value ?? '';
                      if (_selectedCategory.isEmpty) {
                        _searchCtrl.clear();
                        _maxPriceCtrl.clear();
                      }
                      _hasSelection = true;
                    });
                  },
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _maxPriceCtrl,
                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  onChanged: (_) => setState(() => _hasSelection = true),
                  decoration: InputDecoration(
                    labelText: 'Precio maximo',
                    hintText: 'Ej: 40.00',
                    prefixIcon: const Icon(Icons.payments_outlined),
                    suffixIcon: _selectedCategory.isEmpty &&
                            _searchCtrl.text.trim().isEmpty &&
                            _maxPriceCtrl.text.trim().isEmpty
                        ? null
                        : IconButton(
                            onPressed: () {
                              setState(() {
                                _searchCtrl.clear();
                                _maxPriceCtrl.clear();
                                _selectedCategory = '';
                                _hasSelection = false;
                              });
                            },
                            icon: const Icon(Icons.close),
                          ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  static Widget _pill(String text) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(.12),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: Colors.white.withOpacity(.28)),
      ),
      child: Text(
        text,
        style: const TextStyle(
          color: Color(0xFFFFF4E6),
          fontSize: 12,
          fontWeight: FontWeight.w900,
        ),
      ),
    );
  }
}

class _HeroCard extends StatelessWidget {
  const _HeroCard({
    required this.title,
    required this.subtitle,
    required this.product,
    this.fallbackAsset,
  });

  final String title;
  final String subtitle;
  final Producto? product;
  final String? fallbackAsset;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(26),
        border: Border.all(color: Colors.white.withOpacity(.24)),
        boxShadow: const [
          BoxShadow(
            color: Color.fromRGBO(40, 14, 0, .22),
            blurRadius: 26,
            offset: Offset(0, 14),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(26),
        child: Stack(
          fit: StackFit.expand,
          children: [
            if (product != null)
              ProductoImage(
                producto: product!,
                width: double.infinity,
                height: double.infinity,
                fit: BoxFit.cover,
              )
            else
              Image.asset(
                fallbackAsset ?? 'assets/pollooooo.png',
                width: double.infinity,
                height: double.infinity,
                fit: BoxFit.cover,
              ),
            DecoratedBox(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.black.withOpacity(.08),
                    Colors.black.withOpacity(.74),
                  ],
                ),
              ),
            ),
            Positioned(
              left: 16,
              right: 16,
              bottom: 16,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      color: Color(0xFFFFF4EB),
                      fontSize: 22,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    subtitle,
                    style: const TextStyle(
                      color: Color(0xFFFFF4EB),
                      height: 1.4,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ProductCard extends StatelessWidget {
  const _ProductCard({
    required this.product,
    required this.onOpen,
    required this.onAdd,
  });

  final Producto product;
  final VoidCallback onOpen;
  final VoidCallback onAdd;

  @override
  Widget build(BuildContext context) {
    final soldOut = product.stock <= 0;

    return Container(
      decoration: StoreTheme.surfaceDecoration(),
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          ClipRRect(
            borderRadius: BorderRadius.circular(20),
            child: Container(
              color: const Color(0xFFFFF7F0),
              child: ProductoImage(
                producto: product,
                width: double.infinity,
                height: 128,
                fit: BoxFit.contain,
              ),
            ),
          ),
          const SizedBox(height: 10),
          Text(
            product.name,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900),
          ),
          const SizedBox(height: 8),
          HomeTabStateHelpers.categoryPill(product.categoria),
          const SizedBox(height: 8),
          Text(
            'S/ ${product.price.toStringAsFixed(2)}',
            style: const TextStyle(
              color: StoreTheme.orangeDeep,
              fontSize: 24,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 10),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
            decoration: BoxDecoration(
              color: soldOut ? const Color(0xFFFFF1EA) : const Color(0xFFFFF7F0),
              borderRadius: BorderRadius.circular(999),
              border: Border.all(
                color: soldOut
                    ? const Color(0xFFFFC4AF)
                    : StoreTheme.lineStrong.withOpacity(.82),
              ),
            ),
            child: Text(
              soldOut ? 'Platillo agotado' : 'Disponible hoy',
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w900,
                color: soldOut ? const Color(0xFF9A3610) : const Color(0xFF7E451D),
              ),
            ),
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  onPressed: onOpen,
                  child: const Text('Ver detalle'),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: FilledButton(
                  style: FilledButton.styleFrom(
                    backgroundColor: soldOut ? Colors.grey.shade300 : StoreTheme.orange,
                    foregroundColor: StoreTheme.ink,
                  ),
                  onPressed: soldOut ? null : onAdd,
                  child: Text(soldOut ? 'Agotado' : 'Agregar'),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class HomeTabStateHelpers {
  static Widget categoryPill(String category) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
      decoration: BoxDecoration(
        color: const Color(0xFFFFF7F0),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: StoreTheme.lineStrong.withOpacity(.82)),
      ),
      child: Text(
        category,
        style: const TextStyle(
          color: Color(0xFF8A4A1F),
          fontSize: 12,
          fontWeight: FontWeight.w900,
        ),
      ),
    );
  }
}
