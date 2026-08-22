import 'package:flutter/foundation.dart';
import 'package:flutter/widgets.dart';
import '../models/producto.dart';

class CartItem {
  final Producto producto;
  int qty;

  CartItem({required this.producto, this.qty = 1});

  double get total => producto.price * qty;
}

class CartController extends ChangeNotifier {
  final Map<int, CartItem> _items = {};
  bool isDelivery = true;
  bool scheduleNow = true;
  DateTime? scheduledFor;
  String deliveryWindowLabel = '';
  String address = '';
  String reference = '';
  String customerName = '';
  String customerPhone = '';
  String customerEmail = '';
  String orderNote = '';
  String saladType = '';
  double? latitude;
  double? longitude;

  List<CartItem> get items => _items.values.toList();

  int get totalItemsCount => _items.values.fold(0, (a, b) => a + b.qty);

  double get subtotal => _items.values.fold(0.0, (a, b) => a + b.total);

  // Pedidos menores a este monto no califican para delivery (el costo de
  // envio no seria rentable para la empresa en pedidos tan chicos).
  static const double deliveryMinimumSubtotal = 10.0;
  static const double deliveryFeeAmount = 1.0;

  // Piso general de la tienda: la empresa vende pollos y parrillas, no
  // bebidas sueltas. Coincide con el minimo de delivery (aprox. el precio
  // del 1/4 de pollo a la brasa, el producto mas economico del menu).
  static const double minimumOrderSubtotal = deliveryMinimumSubtotal;

  bool get qualifiesForOrder => subtotal >= minimumOrderSubtotal;

  bool get qualifiesForDelivery => subtotal >= deliveryMinimumSubtotal;

  double deliveryFee() => qualifiesForDelivery ? deliveryFeeAmount : 0.0;

  double total() => subtotal + deliveryFee();

  void add(Producto p) {
    final existing = _items[p.id];
    if (existing != null) {
      existing.qty += 1;
    } else {
      _items[p.id] = CartItem(producto: p, qty: 1);
    }
    notifyListeners();
  }

  void removeOne(int productId) {
    final existing = _items[productId];
    if (existing == null) return;
    existing.qty -= 1;
    if (existing.qty <= 0) _items.remove(productId);
    notifyListeners();
  }

  void setQty(int productId, int qty) {
    final existing = _items[productId];
    if (existing == null) return;
    existing.qty = qty.clamp(1, 999);
    notifyListeners();
  }

  void delete(int productId) {
    _items.remove(productId);
    notifyListeners();
  }

  void clear() {
    _items.clear();
    isDelivery = true;
    scheduleNow = true;
    scheduledFor = null;
    deliveryWindowLabel = '';
    address = '';
    reference = '';
    customerName = '';
    customerPhone = '';
    customerEmail = '';
    orderNote = '';
    saladType = '';
    latitude = null;
    longitude = null;
    notifyListeners();
  }

  void setDeliveryType(bool value) {
    isDelivery = value;
    notifyListeners();
  }

  void setScheduleNow(bool value) {
    scheduleNow = value;
    if (value) {
      scheduledFor = null;
      deliveryWindowLabel = '';
    }
    notifyListeners();
  }

  void setScheduledFor(DateTime? value, {String? label}) {
    scheduledFor = value;
    scheduleNow = value == null;
    deliveryWindowLabel = label ?? '';
    notifyListeners();
  }

  void setAddress({
    required String addressValue,
    String? referenceValue,
    double? latitudeValue,
    double? longitudeValue,
  }) {
    address = addressValue;
    reference = referenceValue ?? reference;
    latitude = latitudeValue ?? latitude;
    longitude = longitudeValue ?? longitude;
    notifyListeners();
  }

  void setGuidedCheckout({required String name, required String phone, required String email, required String note, required String salad}) {
    customerName = name;
    customerPhone = phone;
    customerEmail = email;
    orderNote = note.length > 120 ? note.substring(0, 120) : note;
    saladType = salad;
    notifyListeners();
  }
}

/// Provider sin paquetes (InheritedNotifier)
class CartScope extends InheritedNotifier<CartController> {
  const CartScope({super.key, required CartController controller, required super.child})
      : super(notifier: controller);

  static CartController of(context) {
    final scope = context.dependOnInheritedWidgetOfExactType<CartScope>();
    if (scope == null) {
      throw Exception('CartScope no encontrado. Envuélvelo en main.dart');
    }
    return scope.notifier!;
  }
}
