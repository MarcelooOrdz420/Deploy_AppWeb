import '../config/api_config.dart';

class Producto {
  final int id;
  final String name;
  final double price;
  final String description;
  final String image;
  final String categoria;
  final double rating;
  final int stock;

  Producto({
    required this.id,
    required this.name,
    required this.price,
    required this.description,
    required this.image,
    required this.categoria,
    this.rating = 4.5,
    this.stock = 10,
  });

  factory Producto.fromJson(Map<String, dynamic> json) {
    final rawPrice = json['price'];
    final parsedPrice = rawPrice is num
        ? rawPrice.toDouble()
        : double.tryParse((rawPrice ?? '0').toString()) ?? 0.0;
    final rawRating = json['rating'];
    final parsedRating = rawRating is num
        ? rawRating.toDouble()
        : double.tryParse((rawRating ?? '4.5').toString()) ?? 4.5;
    final rawStock = json['stock'];
    final parsedStock = rawStock is num
        ? rawStock.toInt()
        : int.tryParse((rawStock ?? '10').toString()) ?? 10;

    return Producto(
      id: (json['id'] as num).toInt(),
      name: (json['name'] ?? '').toString(),
      price: parsedPrice,
      description: (json['description'] ?? '').toString(),
      image: (json['image_url'] ?? json['image'] ?? '').toString(),
      categoria: (json['category'] ?? json['categoria'] ?? 'pollos').toString(),
      rating: parsedRating,
      stock: parsedStock,
    );
  }

  bool get isNetworkImage {
    final value = image.trim();
    if (value.isEmpty) return false;

    // Si el backend envía rutas relativas tipo `images/...` o `storage/...`,
    // deben resolverse contra el origin (no son assets de Flutter).
    if (value.startsWith('http://') || value.startsWith('https://')) return true;
    if (value.startsWith('/')) return true;
    if (value.contains('/')) return true;

    // Permite rutas explícitas de assets (p.ej. `assets/pollooooo.png`).
    if (value.startsWith('assets/')) return false;

    return false;
  }

  String get resolvedImage {
    final value = image.trim();
    if (isNetworkImage) return ApiConfig.resolveUrl(value);
    if (value.startsWith('assets/')) return value;
    return 'assets/$value';
  }
}
