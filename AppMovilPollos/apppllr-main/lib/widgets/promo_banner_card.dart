import 'dart:async';

import 'package:flutter/material.dart';

import '../config/api_config.dart';
import '../models/promotion_offer.dart';
import '../screens/promotion_page.dart';
import '../theme/store_theme.dart';

/// Caja de promocion destacada, reutilizada tanto en el inicio de la app
/// (logueado/invitado) como en la pantalla de bienvenida previa al login,
/// para que la promocion se vea apenas se abre la app. Cuando hay mas de
/// una promocion vigente (hasta 3), rota sola como un carrusel.
class PromoBannerCard extends StatefulWidget {
  const PromoBannerCard({super.key, required this.offers});

  final List<PromotionOffer> offers;

  @override
  State<PromoBannerCard> createState() => _PromoBannerCardState();
}

class _PromoBannerCardState extends State<PromoBannerCard> {
  final PageController _controller = PageController();
  Timer? _timer;
  int _index = 0;

  @override
  void didUpdateWidget(covariant PromoBannerCard oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.offers.length != widget.offers.length) {
      _index = 0;
      _restartTimer();
    }
  }

  @override
  void initState() {
    super.initState();
    _restartTimer();
  }

  void _restartTimer() {
    _timer?.cancel();
    if (widget.offers.length <= 1) return;
    _timer = Timer.periodic(const Duration(seconds: 6), (_) {
      if (!mounted || !_controller.hasClients) return;
      final next = (_index + 1) % widget.offers.length;
      _controller.animateToPage(
        next,
        duration: const Duration(milliseconds: 420),
        curve: Curves.easeOutCubic,
      );
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    _controller.dispose();
    super.dispose();
  }

  void _goTo(int step) {
    if (widget.offers.length < 2 || !_controller.hasClients) return;
    final next = (_index + step + widget.offers.length) % widget.offers.length;
    _controller.animateToPage(
      next,
      duration: const Duration(milliseconds: 320),
      curve: Curves.easeOutCubic,
    );
    _restartTimer();
  }

  Widget _navButton({required IconData icon, required VoidCallback onTap}) {
    return Material(
      color: Colors.black.withOpacity(.28),
      shape: const CircleBorder(),
      child: InkWell(
        customBorder: const CircleBorder(),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(8),
          child: Icon(icon, color: Colors.white, size: 18),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (widget.offers.isEmpty) {
      return const _EmptyPromoCard();
    }

    return SizedBox(
      height: 240,
      child: Stack(
        children: [
          PageView.builder(
            controller: _controller,
            itemCount: widget.offers.length,
            onPageChanged: (index) => setState(() => _index = index),
            itemBuilder: (context, index) => _PromoOfferCard(offer: widget.offers[index]),
          ),
          if (widget.offers.length > 1) ...[
            Positioned(
              left: 10,
              top: 0,
              bottom: 0,
              child: Center(child: _navButton(icon: Icons.chevron_left_rounded, onTap: () => _goTo(-1))),
            ),
            Positioned(
              right: 10,
              top: 0,
              bottom: 0,
              child: Center(child: _navButton(icon: Icons.chevron_right_rounded, onTap: () => _goTo(1))),
            ),
            Positioned(
              bottom: 14,
              left: 0,
              right: 0,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: List.generate(widget.offers.length, (i) {
                  final active = i == _index;
                  return AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    margin: const EdgeInsets.symmetric(horizontal: 3),
                    width: active ? 16 : 6,
                    height: 6,
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(active ? .95 : .45),
                      borderRadius: BorderRadius.circular(999),
                    ),
                  );
                }),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _EmptyPromoCard extends StatelessWidget {
  const _EmptyPromoCard();

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      height: 240,
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
}

class _PromoOfferCard extends StatelessWidget {
  const _PromoOfferCard({required this.offer});

  final PromotionOffer offer;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => Navigator.of(context).push(
        MaterialPageRoute(builder: (_) => PromotionPage(offer: offer)),
      ),
      child: Container(
        width: double.infinity,
        height: 240,
        clipBehavior: Clip.antiAlias,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(28),
          gradient: const LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [StoreTheme.orangeDark, StoreTheme.orange],
          ),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            SizedBox(
              width: 150,
              child: Image.network(
                ApiConfig.resolveUrl(offer.imageUrl),
                fit: BoxFit.cover,
                alignment: Alignment.center,
                errorBuilder: (_, __, ___) => Container(color: Colors.white.withOpacity(.12)),
              ),
            ),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 18, 16, 18),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.center,
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
                        fontSize: 21,
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
}
