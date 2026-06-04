import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../state/cart_controller.dart';
import '../theme/store_theme.dart';

class OrderConfirmedPage extends StatelessWidget {
  const OrderConfirmedPage({
    super.key,
    this.trackingCode,
    this.totalPaid,
    this.itemsText,
  });

  final String? trackingCode;
  final double? totalPaid;
  final String? itemsText;

  @override
  Widget build(BuildContext context) {
    final cart = CartScope.of(context);
    final resolvedTrackingCode =
        (trackingCode ?? '').trim().isEmpty ? 'Generado por la API' : trackingCode!.trim();
    final resolvedTotal = totalPaid ?? cart.total(freeOver: 70, fee: 4);
    final resolvedItems = (itemsText ?? '').trim().isEmpty
        ? cart.items.map((e) => e.producto.name).join(', ')
        : itemsText!.trim();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Pedido confirmado'),
        backgroundColor: StoreTheme.orange,
        foregroundColor: StoreTheme.ink,
      ),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            const SizedBox(height: 20),
            const Icon(Icons.verified_rounded, color: Color(0xFF2DBF72), size: 90),
            const SizedBox(height: 10),
            const Text('¡Pedido Confirmado!', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
            const SizedBox(height: 12),
            const Text(
              'Tus productos fueron enviados al sistema de la empresa. Te avisaremos cuando el estado cambie.',
              textAlign: TextAlign.center,
              style: TextStyle(color: StoreTheme.inkSoft, height: 1.4),
            ),
            const SizedBox(height: 14),

            Card(
              color: StoreTheme.paper,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Resumen del Pedido', style: TextStyle(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    Text('Codigo: $resolvedTrackingCode', style: const TextStyle(color: Colors.black54)),
                    const SizedBox(height: 6),
                    Text('Articulos: $resolvedItems', style: const TextStyle(color: Colors.black54)),
                    const SizedBox(height: 6),
                    Text('Cantidad de Productos: ${cart.totalItemsCount}', style: const TextStyle(color: Colors.black54)),
                    const Divider(),
                    Row(
                      children: [
                        const Expanded(child: Text('Total Pagado:', style: TextStyle(fontWeight: FontWeight.bold))),
                        Text(
                          'S/. ${resolvedTotal.toStringAsFixed(2)}',
                          style: const TextStyle(
                            color: StoreTheme.orangeDeep,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),

            const Spacer(),

            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: StoreTheme.orange,
                  foregroundColor: StoreTheme.ink,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
                ),
                onPressed: () async {
                  cart.clear();

                  if (context.mounted) context.go('/app');
                },
                child: const Text('Volver al inicio', style: TextStyle(fontWeight: FontWeight.w900)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
