import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../models/producto.dart';
import '../state/cart_controller.dart';
import '../widgets/store_alert_dialog.dart';
import 'company_settings_service.dart';

class _CategoryRule {
  const _CategoryRule(this.max, this.label);
  final int max;
  final String label;
}

String _normalizeName(String name) => name.trim().toLowerCase();

String _normalizeCategory(String category, String name) {
  final n = '${category.toLowerCase()} ${name.toLowerCase()}';
  if (RegExp(r'\bpollo').hasMatch(n)) return 'pollos';
  if (RegExp(r'\b(parrilla|carne|anticucho)').hasMatch(n)) return 'parrillas';
  if (RegExp(r'\b(bebida|gaseosa|refresco)').hasMatch(n)) return 'bebidas';
  return category.toLowerCase();
}

// Mismas reglas que la web (resources/views/store/cart.blade.php y
// products.blade.php): topes exactos por nombre (tope real de
// autoservicio), un tope por categoria que cubre cualquier producto de
// parrillas o bebidas, y un tope mas bajo especifico para la chicha. Los
// pollos enteros y mega combos ademas piden confirmar desde 3 unidades y
// redirigen a contactar al administrador desde 5.
const Map<String, int> _exactLimits = {
  'pollo entero a la brasa': 4,
  'mega combo familiar': 4,
  '1/2 pollo a la brasa': 2,
  '1/4 pollo a la brasa': 3,
  'mostrito tradicional': 4,
};

const List<String> _largeOrderNames = [
  'pollo entero a la brasa',
  'mega combo familiar',
];

const Map<String, _CategoryRule> _categoryLimits = {
  'parrillas': _CategoryRule(2, 'porciones o productos'),
  'bebidas': _CategoryRule(3, 'productos'),
};

const int _chichaMax = 2;

int _currentQty(CartController cart, int productId) {
  final matches = cart.items.where((item) => item.producto.id == productId);
  return matches.isEmpty ? 0 : matches.first.qty;
}

String? _limitErrorFor(CartController cart, Producto product, int addQty) {
  final name = _normalizeName(product.name);
  final nextQty = _currentQty(cart, product.id) + addQty;

  final exactMax = _exactLimits[name];
  if (exactMax != null) {
    return nextQty > exactMax
        ? 'Solo se permiten $exactMax unidades de ${product.name} por pedido. '
              'Para pedidos mas grandes, coordina con el administrador.'
        : null;
  }

  if (name.contains('chicha')) {
    return nextQty > _chichaMax
        ? 'Solo puedes pedir $_chichaMax unidades de "${product.name}" por persona.'
        : null;
  }

  final category = _normalizeCategory(product.categoria, product.name);
  final rule = _categoryLimits[category];
  if (rule != null && nextQty > rule.max) {
    return 'Solo puedes pedir ${rule.max} ${rule.label} por persona de "${product.name}".';
  }
  return null;
}

String _band(int qty) {
  if (qty <= 2) return 'free';
  if (qty <= 4) return 'confirm';
  return 'admin';
}

/// Agrega [quantity] unidades de [product] al carrito, respetando los mismos
/// topes por producto/categoria que la web: bloquea con una alerta si supera
/// el tope real, pide confirmar entre 3 y 4 pollos enteros/mega combos, y
/// ofrece contactar al administrador por WhatsApp (vaciando el carrito) si
/// se intenta pasar de 4. Devuelve true si el producto se agrego de verdad.
Future<bool> addToCartWithLimits(
  BuildContext context,
  CartController cart,
  Producto product, {
  int quantity = 1,
}) async {
  if (quantity <= 0) return false;

  final limitError = _limitErrorFor(cart, product, quantity);
  if (limitError != null) {
    await showStoreAlertDialog(
      context,
      title: 'No se pudo agregar',
      message: limitError,
      icon: Icons.block_rounded,
    );
    return false;
  }

  final previousQty = _currentQty(cart, product.id);
  final nextQty = previousQty + quantity;

  if (_largeOrderNames.contains(_normalizeName(product.name))) {
    final prevBand = _band(previousQty);
    final nextBand = _band(nextQty);
    if (nextBand != prevBand) {
      if (nextBand == 'admin') {
        await _offerAdminContact(context, cart, product, nextQty);
        return false;
      }
      var confirmed = false;
      await showStoreAlertDialog(
        context,
        title: '¿Estas seguro?',
        message:
            'Estas pidiendo $nextQty unidades de "${product.name}". '
            '¿Estas seguro de tu pedido?',
        icon: Icons.help_outline_rounded,
        buttonLabel: 'Mejor no',
        secondaryLabel: 'Si, continuar',
        onSecondary: () => confirmed = true,
      );
      if (!confirmed) return false;
    }
  }

  for (var i = 0; i < quantity; i++) {
    if (product.hasActivePromo) {
      cart.addPromo(
        product,
        promoPrice: product.promoPrice!,
        originalPrice: product.price,
        promotionId: product.promotionId!,
      );
    } else {
      cart.add(product);
    }
  }
  return true;
}

Future<void> _offerAdminContact(
  BuildContext context,
  CartController cart,
  Producto product,
  int nextQty,
) async {
  final settings = await CompanySettingsService().fetch();
  final phone = settings.supportPhone.replaceAll(RegExp(r'\D'), '');
  final message = Uri.encodeComponent(
    'Hola, quiero pedir $nextQty unidades de "${product.name}". '
    '¿Me ayudan a coordinar este pedido?',
  );
  final waUri = phone.isNotEmpty
      ? Uri.parse('https://wa.me/$phone?text=$message')
      : null;

  var wantsContact = false;
  if (!context.mounted) return;
  await showStoreAlertDialog(
    context,
    title: 'Pedido grande: contacta al administrador',
    message:
        'Para pedir $nextQty o mas unidades de "${product.name}" (por '
        'ejemplo para un evento), coordina directamente con el '
        'administrador. Si continuas, tu carrito se vaciara.',
    icon: Icons.support_agent_rounded,
    buttonLabel: 'Cancelar',
    secondaryLabel: waUri != null ? 'Contactar por WhatsApp' : 'Entendido',
    onSecondary: () => wantsContact = true,
  );
  if (!wantsContact) return;
  if (waUri != null) {
    await launchUrl(waUri, mode: LaunchMode.externalApplication);
  }
  cart.clear();
}
