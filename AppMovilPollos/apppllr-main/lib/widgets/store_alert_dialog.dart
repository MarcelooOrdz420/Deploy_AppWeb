import 'package:flutter/material.dart';

import '../theme/store_theme.dart';

/// Dialogo de alerta con el mismo lenguaje visual de la tienda (icono en
/// tarjeta redondeada + boton ancho), en vez del AlertDialog plano por
/// defecto de Material.
Future<void> showStoreAlertDialog(
  BuildContext context, {
  required String title,
  required String message,
  String buttonLabel = 'Entendido',
  IconData icon = Icons.priority_high_rounded,
  String? secondaryLabel,
  VoidCallback? onSecondary,
}) {
  return showDialog<void>(
    context: context,
    builder: (context) => Dialog(
      backgroundColor: Colors.transparent,
      insetPadding: const EdgeInsets.symmetric(horizontal: 28),
      child: Container(
        padding: const EdgeInsets.fromLTRB(24, 26, 24, 20),
        decoration: BoxDecoration(
          color: StoreTheme.paper,
          borderRadius: BorderRadius.circular(26),
          border: Border.all(color: StoreTheme.borderSoft),
          boxShadow: const [
            BoxShadow(
              color: Color.fromRGBO(52, 17, 0, .22),
              blurRadius: 40,
              offset: Offset(0, 20),
            ),
          ],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 48,
              height: 48,
              decoration: BoxDecoration(
                color: const Color(0xFFFFE4D2),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Icon(icon, color: StoreTheme.orangeDeep, size: 26),
            ),
            const SizedBox(height: 14),
            Text(
              title,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w900,
                color: StoreTheme.ink,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              message,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 14,
                height: 1.5,
                color: StoreTheme.inkSoft,
                fontWeight: FontWeight.w600,
              ),
            ),
            const SizedBox(height: 18),
            if (secondaryLabel != null)
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      style: OutlinedButton.styleFrom(
                        foregroundColor: StoreTheme.orangeDeep,
                        side: const BorderSide(color: StoreTheme.orange),
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                        ),
                      ),
                      onPressed: () => Navigator.of(context).pop(),
                      child: Text(buttonLabel),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: FilledButton(
                      style: FilledButton.styleFrom(
                        backgroundColor: StoreTheme.orange,
                        foregroundColor: StoreTheme.ink,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                        ),
                      ),
                      onPressed: () {
                        Navigator.of(context).pop();
                        onSecondary?.call();
                      },
                      child: Text(secondaryLabel),
                    ),
                  ),
                ],
              )
            else
              SizedBox(
                width: double.infinity,
                child: FilledButton(
                  style: FilledButton.styleFrom(
                    backgroundColor: StoreTheme.orange,
                    foregroundColor: StoreTheme.ink,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                    ),
                  ),
                  onPressed: () => Navigator.of(context).pop(),
                  child: Text(buttonLabel),
                ),
              ),
          ],
        ),
      ),
    ),
  );
}
