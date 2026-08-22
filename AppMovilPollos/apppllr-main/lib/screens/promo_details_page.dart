import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../config/api_config.dart';
import '../models/producto.dart';
import '../services/productos_service.dart';
import '../state/app_shell_controller.dart';
import '../state/cart_controller.dart';
import '../theme/store_theme.dart';

class PromoDetailsPage extends StatefulWidget {
  const PromoDetailsPage({
    super.key,
    required this.title,
    required this.message,
    this.body,
    this.imageUrl,
    this.productId,
    this.promotionId,
    this.promoPrice,
    this.originalPrice,
    this.discountPercent,
  });

  final String title;
  final String message;
  final String? body;
  final String? imageUrl;
  final int? productId;
  final int? promotionId;
  final double? promoPrice;
  final double? originalPrice;
  final double? discountPercent;

  static double? _parseDouble(Object? value) {
    if (value == null) return null;
    if (value is num) return value.toDouble();
    return double.tryParse(value.toString());
  }

  static int? _parseInt(Object? value) {
    if (value == null) return null;
    if (value is num) return value.toInt();
    return int.tryParse(value.toString());
  }

  static PromoDetailsPage fromExtra(Object? extra) {
    if (extra is Map) {
      final data = extra.cast<String, dynamic>();
      return PromoDetailsPage(
        title: (data['title'] ?? 'Promoción').toString(),
        message: (data['message'] ?? '').toString(),
        body: (data['body'] ?? '').toString().trim().isEmpty
            ? null
            : (data['body'] ?? '').toString(),
        imageUrl:
            (data['image_url'] ?? data['imageUrl'] ?? data['image'] ?? '')
                .toString()
                .trim()
                .isEmpty
            ? null
            : (data['image_url'] ?? data['imageUrl'] ?? data['image'])
                .toString(),
        productId: _parseInt(data['product_id'] ?? data['productId']),
        promotionId: _parseInt(data['offer_id'] ?? data['promotion_id']),
        promoPrice: _parseDouble(data['promo_price']),
        originalPrice: _parseDouble(data['original_price']),
        discountPercent: _parseDouble(data['discount_percent']),
      );
    }

    return const PromoDetailsPage(title: 'Promoción', message: '');
  }

  @override
  State<PromoDetailsPage> createState() => _PromoDetailsPageState();
}

class _PromoDetailsPageState extends State<PromoDetailsPage> {
  final _productosService = ProductosService();
  Producto? _producto;
  bool _loading = false;
  String? _error;
  bool _added = false;

  bool get _hasPromo =>
      widget.productId != null &&
      widget.promotionId != null &&
      widget.promoPrice != null;

  @override
  void initState() {
    super.initState();
    if (_hasPromo) _loadProduct();
  }

  Future<void> _loadProduct() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final producto = await _productosService.obtener(widget.productId!);
      if (!mounted) return;
      setState(() => _producto = producto);
    } catch (e) {
      if (!mounted) return;
      setState(
        () => _error = 'No se pudo cargar el platillo de esta promoción.',
      );
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _buyWithDiscount() {
    final producto = _producto;
    if (producto == null) return;
    CartScope.of(context).addPromo(
      producto,
      promoPrice: widget.promoPrice!,
      originalPrice: widget.originalPrice ?? producto.price,
      promotionId: widget.promotionId!,
    );
    setState(() => _added = true);
    AppShellController.instance.goTo(2);
    Future.microtask(() {
      if (!mounted) return;
      context.go('/app');
    });
  }

  @override
  Widget build(BuildContext context) {
    final resolvedImage = ApiConfig.resolveUrl(widget.imageUrl);

    return StoreBackdrop(
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Promoción'),
          backgroundColor: Colors.orange,
          leading: IconButton(
            icon: const Icon(Icons.arrow_back),
            onPressed: () => context.pop(),
          ),
        ),
        body: SafeArea(
          child: StoreFrame(
            child: ListView(
              padding: const EdgeInsets.all(14),
              children: [
                Text(
                  widget.title,
                  style: const TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                const SizedBox(height: 10),
                if ((widget.imageUrl ?? '').trim().isNotEmpty) ...[
                  ClipRRect(
                    borderRadius: BorderRadius.circular(18),
                    child: Image.network(
                      resolvedImage,
                      height: 190,
                      width: double.infinity,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Image.asset(
                        'assets/pollooooo.png',
                        height: 190,
                        width: double.infinity,
                        fit: BoxFit.cover,
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                ],
                if (widget.message.trim().isNotEmpty)
                  Text(
                    widget.message.trim(),
                    style: const TextStyle(color: StoreTheme.ink, height: 1.5),
                  ),
                if ((widget.body ?? '').trim().isNotEmpty) ...[
                  const SizedBox(height: 10),
                  Text(
                    widget.body!.trim(),
                    style: const TextStyle(
                      color: StoreTheme.inkSoft,
                      height: 1.55,
                    ),
                  ),
                ],
                if (_hasPromo) ...[
                  const SizedBox(height: 18),
                  if (_loading)
                    const Center(child: CircularProgressIndicator())
                  else if (_error != null)
                    Text(_error!, style: const TextStyle(color: Colors.red))
                  else if (_producto != null) ...[
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text(
                          'Antes S/ ${(widget.originalPrice ?? _producto!.price).toStringAsFixed(2)}',
                          style: const TextStyle(
                            color: StoreTheme.inkSoft,
                            decoration: TextDecoration.lineThrough,
                          ),
                        ),
                        const SizedBox(width: 10),
                        Text(
                          'S/ ${widget.promoPrice!.toStringAsFixed(2)}',
                          style: const TextStyle(
                            color: StoreTheme.orangeDeep,
                            fontSize: 28,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    FilledButton(
                      style: FilledButton.styleFrom(
                        backgroundColor: StoreTheme.orange,
                        foregroundColor: StoreTheme.ink,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                      ),
                      onPressed: _added ? null : _buyWithDiscount,
                      child: Text(
                        _added ? 'Agregado al carrito' : 'Comprar con descuento',
                      ),
                    ),
                    const SizedBox(height: 10),
                  ],
                ],
                FilledButton(
                  style: FilledButton.styleFrom(
                    backgroundColor: StoreTheme.paper,
                    foregroundColor: StoreTheme.ink,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    side: const BorderSide(color: StoreTheme.orange),
                  ),
                  onPressed: () => context.pop(),
                  child: const Text('Seguir viendo'),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
