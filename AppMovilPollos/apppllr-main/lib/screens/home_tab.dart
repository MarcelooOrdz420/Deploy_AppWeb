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

class _HomeTabState extends State<HomeTab> {
  late Future<List<Producto>> _future;
  final TextEditingController _searchCtrl = TextEditingController();
  final TextEditingController _maxPriceCtrl = TextEditingController();

  String _userName = 'Invitado';
  String _selectedCategory = '';
  bool _logged = false;

  @override
  void initState() {
    super.initState();
    _future = ProductosService().listar();
    _loadSession();
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
    _searchCtrl.dispose();
    _maxPriceCtrl.dispose();
    super.dispose();
  }

  List<Producto> _filteredProducts(List<Producto> products) {
    final query = _searchCtrl.text.trim().toLowerCase();
    final maxPrice = double.tryParse(_maxPriceCtrl.text.trim());

    return products.where((product) {
      final matchesName = query.isEmpty ||
          product.name.toLowerCase().contains(query) ||
          product.categoria.toLowerCase().contains(query);
      final matchesCategory =
          _selectedCategory.isEmpty || product.categoria.toLowerCase() == _selectedCategory;
      final matchesPrice = maxPrice == null || product.price <= maxPrice;
      return matchesName && matchesCategory && matchesPrice;
    }).toList();
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
        final pollos = products.where((item) => item.categoria.toLowerCase() == 'pollos').toList();
        final bebidas = products.where((item) => item.categoria.toLowerCase() == 'bebidas').toList();

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
              _buildHeroSection(
                pollos: pollos,
                bebidas: bebidas,
              ),
              const SizedBox(height: 16),
              _buildFilterSection(filtered.length),
              const SizedBox(height: 16),
              if (_searchCtrl.text.trim().isEmpty &&
                  _selectedCategory.isEmpty &&
                  _maxPriceCtrl.text.trim().isEmpty)
                const StoreSurface(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Explora el menu',
                        style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900),
                      ),
                      SizedBox(height: 8),
                      Text(
                        'Empieza por una busqueda o una categoria para activar el catalogo, igual que en la web.',
                        style: TextStyle(color: StoreTheme.inkSoft, height: 1.5),
                      ),
                    ],
                  ),
                )
              else if (filtered.isEmpty)
                const StoreSurface(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Sin coincidencias',
                        style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900),
                      ),
                      SizedBox(height: 8),
                      Text(
                        'Prueba con otro nombre, otra categoria o un precio mas alto.',
                        style: TextStyle(color: StoreTheme.inkSoft, height: 1.5),
                      ),
                    ],
                  ),
                )
              else
                LayoutBuilder(
                  builder: (context, constraints) {
                    final isWide = constraints.maxWidth >= 720;
                    return GridView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                        crossAxisCount: isWide ? 3 : 2,
                        crossAxisSpacing: 14,
                        mainAxisSpacing: 14,
                        mainAxisExtent: isWide ? 400 : 440,
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

    return StoreSurface(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 54,
                height: 54,
                padding: const EdgeInsets.all(7),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(18),
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
                        color: Color(0xFF9B5A2C),
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'Pollos y Parrillas "El Dorado"',
                      style: TextStyle(fontSize: 24, fontWeight: FontWeight.w900),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      'Hola, $_userName',
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
                      foregroundColor: StoreTheme.ink,
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
              color: const Color(0xFFFFF7F0),
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: StoreTheme.lineStrong.withOpacity(.82)),
            ),
            child: const Row(
              children: [
                Icon(Icons.menu_book_outlined, color: StoreTheme.orangeDeep),
                SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'Busca tu plato favorito y agregalo al carrito.',
                    style: TextStyle(
                      color: StoreTheme.inkSoft,
                      fontWeight: FontWeight.w700,
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

  Widget _buildHeroSection({
    required List<Producto> pollos,
    required List<Producto> bebidas,
  }) {
    final primary = pollos.isNotEmpty ? pollos.first : null;
    final secondary = pollos.length > 1 ? pollos[1] : primary;
    final drinks = bebidas.isNotEmpty ? bebidas.first : secondary;

    return StoreSurface(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Menu del Cliente',
            style: TextStyle(
              fontSize: 11,
              letterSpacing: 2.2,
              color: Color(0xFF9B5A2C),
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Compra en una ruta mas clara, mas visual y mas rapida.',
            style: TextStyle(fontSize: 28, height: 1.02, fontWeight: FontWeight.w900),
          ),
          const SizedBox(height: 10),
          const Text(
            'Explora el menu como una vitrina: descubre destacados, filtra con precision y agrega al carrito sin perder el contexto.',
            style: TextStyle(color: StoreTheme.inkSoft, height: 1.5),
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
          SizedBox(
            height: 360,
            child: Column(
              children: [
                Expanded(
                  child: _HeroCard(
                    title: primary?.name ?? 'Brasa protagonista',
                    subtitle: primary?.description.isNotEmpty == true
                        ? primary!.description
                        : 'Porciones personales con textura crocante y sabor de casa.',
                    product: primary,
                  ),
                ),
                const SizedBox(height: 12),
                Expanded(
                  child: Row(
                    children: [
                      Expanded(
                        child: _HeroCard(
                          title: secondary?.name ?? 'Combos para compartir',
                          subtitle: secondary?.description.isNotEmpty == true
                              ? secondary!.description
                              : 'Medios y enteros listos para familia o grupo.',
                          product: secondary,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: _HeroCard(
                          title: drinks?.name ?? 'Bebidas frias',
                          subtitle: drinks?.description.isNotEmpty == true
                              ? drinks!.description
                              : 'El cierre exacto para cualquier pedido.',
                          product: drinks,
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
                  '$resultsCount resultado(s)',
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
                  onChanged: (_) => setState(() {}),
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
                    DropdownMenuItem(value: 'pollos', child: Text('Pollos')),
                    DropdownMenuItem(value: 'parrillas', child: Text('Parrillas')),
                    DropdownMenuItem(value: 'bebidas', child: Text('Bebidas')),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedCategory = value ?? '';
                    });
                  },
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _maxPriceCtrl,
                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  onChanged: (_) => setState(() {}),
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
        color: const Color(0xFFFFF7F0),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: StoreTheme.lineStrong.withOpacity(.82)),
      ),
      child: Text(
        text,
        style: const TextStyle(
          color: Color(0xFF82471F),
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
  });

  final String title;
  final String subtitle;
  final Producto? product;

  @override
  Widget build(BuildContext context) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(24),
      child: Stack(
        fit: StackFit.expand,
        children: [
          if (product != null)
            ProductoImage(
              producto: product!,
              width: double.infinity,
              height: double.infinity,
            )
          else
            Container(color: const Color(0xFFFFE5CE)),
          DecoratedBox(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [
                  Colors.black.withOpacity(.1),
                  Colors.black.withOpacity(.72),
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
                    fontSize: 20,
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
