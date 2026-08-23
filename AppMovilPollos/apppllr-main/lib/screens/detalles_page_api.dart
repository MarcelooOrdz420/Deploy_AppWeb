import 'package:flutter/material.dart';
import '../models/producto.dart';
import '../services/cart_limits.dart';
import '../services/productos_service.dart';
import '../state/cart_controller.dart';
import '../theme/store_theme.dart';
import '../widgets/producto_image.dart';

class DetallesPageApi extends StatefulWidget {
  final int productId;
  const DetallesPageApi({super.key, required this.productId});

  @override
  State<DetallesPageApi> createState() => _DetallesPageApiState();
}

class _DetallesPageApiState extends State<DetallesPageApi> {
  late Future<Producto> _future;

  @override
  void initState() {
    super.initState();
    _future = ProductosService().obtener(widget.productId);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Detalle')),
      body: FutureBuilder<Producto>(
        future: _future,
        builder: (context, snap) {
          if (snap.connectionState != ConnectionState.done) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snap.hasError) {
            return Center(child: Text('Error: ${snap.error}'));
          }

          final p = snap.data!;
          return Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                ProductoImage(
                  producto: p,
                  height: 250,
                  width: double.infinity,
                ),
                const SizedBox(height: 16),
                Text(p.name,
                    style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold)),
                const SizedBox(height: 8),
                Text('S/ ${p.price.toStringAsFixed(2)}',
                    style: const TextStyle(fontSize: 20, color: Colors.orange)),
                const SizedBox(height: 16),
                Text(p.description,
                    style: const TextStyle(fontSize: 16, color: Colors.black87)),
                const SizedBox(height: 24),
                SizedBox(
                  width: double.infinity,
                  child: FilledButton(
                    style: FilledButton.styleFrom(
                      backgroundColor: StoreTheme.orange,
                      foregroundColor: StoreTheme.ink,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                    ),
                    onPressed: () async {
                      final added = await addToCartWithLimits(
                        context,
                        CartScope.of(context),
                        p,
                      );
                      if (!added || !context.mounted) return;
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(content: Text('${p.name} agregado al carrito')),
                      );
                    },
                    child: const Text('Agregar al carrito'),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
