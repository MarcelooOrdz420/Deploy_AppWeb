import 'dart:async';

import 'package:flutter/material.dart';

import '../config/api_config.dart';
import '../models/promotion_offer.dart';
import '../state/app_shell_controller.dart';
import '../state/cart_controller.dart';
import '../theme/store_theme.dart';

class PromotionPage extends StatefulWidget {
  const PromotionPage({super.key, required this.offer});

  final PromotionOffer offer;

  @override
  State<PromotionPage> createState() => _PromotionPageState();
}

class _PromotionPageState extends State<PromotionPage> {
  Timer? _timer;
  String _countdown = '';

  @override
  void initState() {
    super.initState();
    _tickCountdown();
    _timer = Timer.periodic(const Duration(seconds: 30), (_) => _tickCountdown());
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  void _tickCountdown() {
    final endsAt = widget.offer.endsAt;
    if (endsAt == null) {
      if (mounted) setState(() => _countdown = '');
      return;
    }
    final diff = endsAt.difference(DateTime.now());
    if (diff.isNegative) {
      if (mounted) setState(() => _countdown = 'Promoción finalizada');
      _timer?.cancel();
      return;
    }
    final hours = diff.inHours;
    final minutes = diff.inMinutes % 60;
    if (mounted) {
      setState(() => _countdown = 'Termina en ${hours > 0 ? '${hours}h ' : ''}${minutes}min');
    }
  }

  @override
  Widget build(BuildContext context) {
    final offer = widget.offer;
    final imageUrl = ApiConfig.resolveUrl(offer.imageUrl);

    return Scaffold(
      backgroundColor: StoreTheme.creamStrong,
      appBar: AppBar(title: const Text('Promoción')),
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            AspectRatio(
              aspectRatio: 4 / 3,
              child: Image.network(
                imageUrl,
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) => Container(
                  color: StoreTheme.field,
                  alignment: Alignment.center,
                  child: const Icon(Icons.image_not_supported_outlined, size: 48),
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: StoreTheme.orangeDark,
                      borderRadius: BorderRadius.circular(999),
                    ),
                    child: Text(
                      '-${offer.discountPercent.round()}% PROMOCIÓN',
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w900,
                        fontSize: 12,
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    offer.title,
                    style: const TextStyle(fontSize: 26, fontWeight: FontWeight.w900),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    offer.product.name,
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: StoreTheme.textSecondary),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    offer.body?.isNotEmpty == true ? offer.body! : offer.message,
                    style: const TextStyle(fontSize: 14, color: StoreTheme.textSecondary, height: 1.5),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(
                        'Antes S/ ${offer.originalPrice.toStringAsFixed(2)}',
                        style: const TextStyle(
                          decoration: TextDecoration.lineThrough,
                          color: StoreTheme.textMuted,
                          fontSize: 16,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Text(
                        'S/ ${offer.promoPrice.toStringAsFixed(2)}',
                        style: const TextStyle(
                          color: StoreTheme.orangeDark,
                          fontSize: 30,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                    ],
                  ),
                  if (_countdown.isNotEmpty) ...[
                    const SizedBox(height: 6),
                    Text(
                      _countdown,
                      style: const TextStyle(color: StoreTheme.orangeDark, fontWeight: FontWeight.w700),
                    ),
                  ],
                  const SizedBox(height: 24),
                  SizedBox(
                    width: double.infinity,
                    child: FilledButton(
                      style: FilledButton.styleFrom(
                        backgroundColor: StoreTheme.orange,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 16),
                      ),
                      onPressed: _countdown == 'Promoción finalizada' ? null : _addWithDiscount,
                      child: const Text('Agregar con descuento'),
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

  void _addWithDiscount() {
    final offer = widget.offer;
    CartScope.of(context).addPromo(
      offer.product,
      promoPrice: offer.promoPrice,
      originalPrice: offer.originalPrice,
      promotionId: offer.id,
    );
    final messenger = ScaffoldMessenger.of(context);
    Navigator.of(context).pop();
    AppShellController.instance.goTo(2);
    messenger.showSnackBar(
      SnackBar(content: Text('${offer.product.name} agregado con descuento')),
    );
  }
}
