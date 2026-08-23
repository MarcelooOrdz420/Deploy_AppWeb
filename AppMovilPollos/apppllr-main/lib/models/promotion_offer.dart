import 'producto.dart';

class PromotionOffer {
  final int id;
  final String title;
  final String message;
  final String? body;
  final String imageUrl;
  final double originalPrice;
  final double promoPrice;
  final double discountPercent;
  final DateTime? endsAt;
  final Producto product;

  PromotionOffer({
    required this.id,
    required this.title,
    required this.message,
    required this.body,
    required this.imageUrl,
    required this.originalPrice,
    required this.promoPrice,
    required this.discountPercent,
    required this.endsAt,
    required this.product,
  });

  factory PromotionOffer.fromJson(Map<String, dynamic> json) {
    final productJson = (json['product'] as Map?)?.cast<String, dynamic>() ?? const {};
    return PromotionOffer(
      id: (json['id'] as num).toInt(),
      title: (json['title'] ?? '').toString(),
      message: (json['message'] ?? '').toString(),
      body: json['body']?.toString(),
      imageUrl: (json['image_url'] ?? '').toString(),
      originalPrice: (json['original_price'] as num?)?.toDouble() ?? 0.0,
      promoPrice: (json['promo_price'] as num?)?.toDouble() ?? 0.0,
      discountPercent: (json['discount_percent'] as num?)?.toDouble() ?? 0.0,
      endsAt: json['ends_at'] != null ? DateTime.tryParse(json['ends_at'].toString()) : null,
      product: Producto.fromJson(productJson),
    );
  }
}
