import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../config/api_config.dart';
import '../theme/store_theme.dart';

class PromoDetailsPage extends StatelessWidget {
  const PromoDetailsPage({
    super.key,
    required this.title,
    required this.message,
    this.body,
    this.imageUrl,
  });

  final String title;
  final String message;
  final String? body;
  final String? imageUrl;

  static PromoDetailsPage fromExtra(Object? extra) {
    if (extra is Map) {
      final data = extra.cast<String, dynamic>();
      return PromoDetailsPage(
        title: (data['title'] ?? 'Promoción').toString(),
        message: (data['message'] ?? '').toString(),
        body: (data['body'] ?? '').toString().trim().isEmpty ? null : (data['body'] ?? '').toString(),
        imageUrl: (data['image_url'] ?? data['imageUrl'] ?? data['image'] ?? '').toString().trim().isEmpty
            ? null
            : (data['image_url'] ?? data['imageUrl'] ?? data['image']).toString(),
      );
    }

    return const PromoDetailsPage(
      title: 'Promoción',
      message: '',
    );
  }

  @override
  Widget build(BuildContext context) {
    final resolvedImage = ApiConfig.resolveUrl(imageUrl);

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
                  title,
                  style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900),
                ),
                const SizedBox(height: 10),
                if ((imageUrl ?? '').trim().isNotEmpty) ...[
                  ClipRRect(
                    borderRadius: BorderRadius.circular(18),
                    child: Image.network(
                      resolvedImage,
                      height: 190,
                      width: double.infinity,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => const SizedBox.shrink(),
                    ),
                  ),
                  const SizedBox(height: 12),
                ],
                if (message.trim().isNotEmpty)
                  Text(
                    message.trim(),
                    style: const TextStyle(color: StoreTheme.ink, height: 1.5),
                  ),
                if ((body ?? '').trim().isNotEmpty) ...[
                  const SizedBox(height: 10),
                  Text(
                    body!.trim(),
                    style: const TextStyle(color: StoreTheme.inkSoft, height: 1.55),
                  ),
                ],
                const SizedBox(height: 18),
                FilledButton(
                  style: FilledButton.styleFrom(
                    backgroundColor: StoreTheme.orange,
                    foregroundColor: StoreTheme.ink,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                  ),
                  onPressed: () => context.pop(),
                  child: const Text('Listo'),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

