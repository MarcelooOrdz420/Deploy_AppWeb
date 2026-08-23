import 'dart:async';

import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../config/api_config.dart';
import '../models/producto.dart';
import '../models/promotion_offer.dart';
import '../services/cart_limits.dart';
import '../services/productos_service.dart';
import '../services/promotion_service.dart';
import '../services/session_service.dart';
import '../state/app_shell_controller.dart';
import '../state/cart_controller.dart';
import '../theme/store_theme.dart';
import '../widgets/producto_image.dart';
import '../widgets/store_async_state.dart';
import 'promotion_page.dart';

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
  final ScrollController _pageController = ScrollController();
  final GlobalKey _catalogKey = GlobalKey();
  Timer? _heroTimer;
  int _heroPage = 0;

  String _userName = 'Invitado';
  String _selectedCategory = '';
  bool _hasSelection = false;
  bool _logged = false;

  PromotionOffer? _activePromotion;
  bool _promotionChecked = false;

  @override
  void initState() {
    super.initState();
    _future = ProductosService().listar();
    _loadSession();
    _loadPromotion();
    AppShellController.instance.promotionRefreshTick.addListener(_loadPromotion);
    WidgetsBinding.instance.addObserver(this);
    _startHeroTimer();
  }

  Future<void> _loadPromotion() async {
    final offer = await PromotionService().fetchActive();
    if (!mounted) return;
    setState(() {
      _activePromotion = offer;
      _promotionChecked = true;
    });
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
    AppShellController.instance.promotionRefreshTick.removeListener(_loadPromotion);
    WidgetsBinding.instance.removeObserver(this);
    _heroTimer?.cancel();
    _heroController.dispose();
    _pageController.dispose();
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
      final matchesName =
          query.isEmpty ||
          _normalize(product.name).contains(query) ||
          _normalize(product.categoria).contains(query);
      final matchesCategory =
          _selectedCategory.isEmpty ||
          _normalizeCategory(product.categoria) == _selectedCategory;
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
    if (normalized.contains('parrilla') || normalized.contains('anticucho'))
      return 'parrillas';
    if (normalized.contains('bebida') || normalized.contains('gaseosa'))
      return 'bebidas';
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
    final added = await addToCartWithLimits(context, cart, product);
    if (!added || !context.mounted) return;
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
                            product.description.isEmpty
                                ? 'Sin descripcion.'
                                : product.description,
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
                                padding: const EdgeInsets.symmetric(
                                  vertical: 14,
                                ),
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
            message:
                'Estamos preparando la vitrina para mostrarte el catalogo.',
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
        final pollos = products
            .where((item) => _normalizeCategory(item.categoria) == 'pollos')
            .toList();
        final bebidas = products
            .where((item) => _normalizeCategory(item.categoria) == 'bebidas')
            .toList();
        final parrillas = products
            .where((item) => _normalizeCategory(item.categoria) == 'parrillas')
            .toList();

        return RefreshIndicator(
          onRefresh: () async {
            setState(() {
              _future = ProductosService().listar();
            });
            await _future;
          },
          child: ListView(
            controller: _pageController,
            padding: const EdgeInsets.fromLTRB(14, 12, 14, 16),
            children: [
              _buildTopBar(context),
              const SizedBox(height: 22),
              const Text(
                'Elige tu favorito',
                style: TextStyle(
                  fontSize: 28,
                  fontWeight: FontWeight.w900,
                  letterSpacing: -.7,
                ),
              ),
              const SizedBox(height: 14),
              Row(
                children: [
                  Expanded(
                    child: _categoryShortcut(
                      'Pollos',
                      Icons.local_fire_department_rounded,
                      'pollos',
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: _categoryShortcut(
                      'Parrillas',
                      Icons.outdoor_grill_rounded,
                      'parrillas',
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: _categoryShortcut(
                      'Bebidas',
                      Icons.local_drink_rounded,
                      'bebidas',
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 22),
              if (_promotionChecked) _buildPromoBox(),
              if (_promotionChecked) const SizedBox(height: 22),
              _buildHeroCarousel(
                pollos: pollos,
                bebidas: bebidas,
                parrillas: parrillas,
              ),
              const SizedBox(height: 22),
              Container(key: _catalogKey),
              Text(
                _selectedCategory.isEmpty
                    ? 'Nuestro menú'
                    : 'Productos · ${_selectedCategory[0].toUpperCase()}${_selectedCategory.substring(1)}',
                style: const TextStyle(
                  fontSize: 28,
                  fontWeight: FontWeight.w900,
                  letterSpacing: -.7,
                ),
              ),
              const SizedBox(height: 14),
              if (!_hasSelection)
                const StoreSurface(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Elige una categoria para ver los productos.',
                        style: TextStyle(
                          fontSize: 22,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                      SizedBox(height: 8),
                      Text(
                        'Selecciona Pollos, Parrillas, Bebidas o Todas.',
                        style: TextStyle(
                          color: StoreTheme.inkSoft,
                          height: 1.5,
                        ),
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
    final cartCount = CartScope.of(
      context,
    ).items.fold<int>(0, (sum, item) => sum + item.qty);

    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(28),
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Colors.white, Color(0xFFFFF8F2), Color(0xFFFFE6D2)],
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
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    SizedBox(
                      width: 48,
                      height: 48,
                      child: Image.asset('assets/pollia.webp'),
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
                              color: StoreTheme.orangeDeep,
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
                              color: StoreTheme.ink,
                            ),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            'Hola, $_userName',
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(color: StoreTheme.inkSoft),
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
                            backgroundColor: StoreTheme.orange,
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
                Material(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(18),
                  child: InkWell(
                    borderRadius: BorderRadius.circular(18),
                    onTap: () {
                      AppShellController.instance.goTo(1);
                      context.go('/app');
                    },
                    child: Padding(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 14,
                        vertical: 12,
                      ),
                      child: const Row(
                        children: [
                          Icon(
                            Icons.search_rounded,
                            color: StoreTheme.orangeDeep,
                          ),
                          SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              '¿Qué se te antoja hoy?',
                              style: TextStyle(
                                color: StoreTheme.ink,
                                fontWeight: FontWeight.w900,
                              ),
                            ),
                          ),
                        ],
                      ),
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

  Widget _categoryShortcut(String label, IconData icon, String category) {
    final selected = _selectedCategory == category;
    return Material(
      color: selected ? const Color(0xFFFFE4D2) : Colors.white,
      borderRadius: BorderRadius.circular(24),
      child: InkWell(
        borderRadius: BorderRadius.circular(24),
        onTap: () {
          setState(() {
            _selectedCategory = category;
            _hasSelection = true;
            _searchCtrl.clear();
            _maxPriceCtrl.clear();
          });
          WidgetsBinding.instance.addPostFrameCallback((_) {
            final target = _catalogKey.currentContext;
            if (!mounted || target == null) return;
            Scrollable.ensureVisible(
              target,
              duration: const Duration(milliseconds: 420),
              curve: Curves.easeOutCubic,
              alignment: .04,
            );
          });
        },
        child: Container(
          height: 116,
          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 14),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(24),
            boxShadow: const [
              BoxShadow(
                color: Color.fromRGBO(25, 22, 20, .05),
                blurRadius: 18,
                offset: Offset(0, 8),
              ),
            ],
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, color: StoreTheme.orangeDeep, size: 34),
              const SizedBox(height: 10),
              Text(
                label,
                textAlign: TextAlign.center,
                style: const TextStyle(fontWeight: FontWeight.w900),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildPromoBox() {
    final offer = _activePromotion;
    if (offer == null) {
      return Container(
        width: double.infinity,
        height: 220,
        alignment: Alignment.center,
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [StoreTheme.creamStrong, StoreTheme.goldSoft],
          ),
          borderRadius: BorderRadius.circular(28),
          border: Border.all(color: StoreTheme.border),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: const [
            Icon(Icons.local_offer_outlined, color: StoreTheme.orangeDark, size: 40),
            SizedBox(height: 10),
            Text(
              'Pronto más descuentos en nuestros productos',
              textAlign: TextAlign.center,
              style: TextStyle(fontWeight: FontWeight.w800, color: StoreTheme.textPrimary, fontSize: 15),
            ),
          ],
        ),
      );
    }

    return GestureDetector(
      onTap: () => Navigator.of(context).push(
        MaterialPageRoute(builder: (_) => PromotionPage(offer: offer)),
      ),
      child: Container(
        width: double.infinity,
        height: 260,
        clipBehavior: Clip.antiAlias,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(28),
          gradient: const LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [StoreTheme.orangeDark, StoreTheme.orange],
          ),
        ),
        child: Stack(
          fit: StackFit.expand,
          children: [
            Image.network(
              ApiConfig.resolveUrl(offer.imageUrl),
              fit: BoxFit.cover,
              alignment: Alignment.center,
              errorBuilder: (_, __, ___) => const SizedBox.shrink(),
            ),
            DecoratedBox(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.black.withOpacity(.05),
                    Colors.black.withOpacity(.55),
                    Colors.black.withOpacity(.82),
                  ],
                ),
              ),
            ),
            Align(
              alignment: Alignment.bottomLeft,
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 18),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(.2),
                        borderRadius: BorderRadius.circular(999),
                        border: Border.all(color: Colors.white.withOpacity(.32)),
                      ),
                      child: Text(
                        '-${offer.discountPercent.round()}% de descuento',
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w900,
                          fontSize: 11,
                          letterSpacing: .4,
                        ),
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      offer.title,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w900,
                        fontSize: 24,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      offer.product.name,
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w700,
                        fontSize: 13,
                      ),
                    ),
                    if ((offer.body ?? offer.message).isNotEmpty) ...[
                      const SizedBox(height: 4),
                      Text(
                        offer.body?.isNotEmpty == true ? offer.body! : offer.message,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          color: Colors.white.withOpacity(.85),
                          fontSize: 12.5,
                          height: 1.35,
                        ),
                      ),
                    ],
                    const SizedBox(height: 8),
                    Text(
                      'S/ ${offer.originalPrice.toStringAsFixed(2)}   →   S/ ${offer.promoPrice.toStringAsFixed(2)}',
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w900,
                        fontSize: 17,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
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
              child: Text(
                'Promociones destacadas',
                style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800),
              ),
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
                      imageFit: index == 1 ? BoxFit.contain : BoxFit.cover,
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
    this.imageFit = BoxFit.cover,
  });

  final String title;
  final String subtitle;
  final Producto? product;
  final String? fallbackAsset;
  final BoxFit imageFit;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: imageFit == BoxFit.contain ? Colors.white : null,
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
                fit: imageFit,
              )
            else
              Image.asset(
                fallbackAsset ?? 'assets/pollooooo.png',
                width: double.infinity,
                height: double.infinity,
                fit: imageFit,
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
              color: soldOut
                  ? const Color(0xFFFFF1EA)
                  : const Color(0xFFFFF7F0),
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
                color: soldOut
                    ? const Color(0xFF9A3610)
                    : const Color(0xFF7E451D),
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
                    backgroundColor: soldOut
                        ? Colors.grey.shade300
                        : StoreTheme.orange,
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
