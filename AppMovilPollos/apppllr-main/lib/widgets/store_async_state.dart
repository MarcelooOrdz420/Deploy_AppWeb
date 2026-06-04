import 'package:flutter/material.dart';

import '../theme/store_theme.dart';

class StoreAsyncState extends StatelessWidget {
  const StoreAsyncState({
    super.key,
    required this.icon,
    required this.title,
    required this.message,
    this.actionLabel,
    this.onAction,
  });

  final IconData icon;
  final String title;
  final String message;
  final String? actionLabel;
  final VoidCallback? onAction;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: StoreSurface(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, size: 46, color: StoreTheme.orange),
              const SizedBox(height: 12),
              Text(
                title,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w900,
                  color: StoreTheme.ink,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                message,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: StoreTheme.inkSoft,
                  height: 1.5,
                ),
              ),
              if ((actionLabel ?? '').trim().isNotEmpty && onAction != null) ...[
                const SizedBox(height: 14),
                FilledButton(
                  style: FilledButton.styleFrom(
                    backgroundColor: StoreTheme.orange,
                    foregroundColor: StoreTheme.ink,
                  ),
                  onPressed: onAction,
                  child: Text(actionLabel!.trim()),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
