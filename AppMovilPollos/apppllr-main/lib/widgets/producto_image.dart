import 'package:flutter/material.dart';
import '../models/producto.dart';

class ProductoImage extends StatelessWidget {
  final Producto producto;
  final double? width;
  final double? height;
  final BoxFit fit;
  final BorderRadius? borderRadius;

  const ProductoImage({
    super.key,
    required this.producto,
    this.width,
    this.height,
    this.fit = BoxFit.cover,
    this.borderRadius,
  });

  @override
  Widget build(BuildContext context) {
    final imageWidget = producto.isNetworkImage
      ? Image.network(
            producto.resolvedImage,
            width: width,
            height: height,
            fit: fit,
            webHtmlElementStrategy: WebHtmlElementStrategy.prefer,
            errorBuilder: (_, __, ___) => _fallback(),
          )
        : Image.asset(
            producto.resolvedImage,
            width: width,
            height: height,
            fit: fit,
            errorBuilder: (_, __, ___) => _fallback(),
          );

    if (borderRadius == null) return imageWidget;

    return ClipRRect(
      borderRadius: borderRadius!,
      child: imageWidget,
    );
  }

  Widget _fallback() {
    return Container(
      width: width,
      height: height,
      color: const Color(0xFFFFF1E4),
      alignment: Alignment.center,
      child: const Icon(Icons.image_not_supported_outlined, color: Colors.orange),
    );
  }
}
