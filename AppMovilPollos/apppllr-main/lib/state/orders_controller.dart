import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:flutter/widgets.dart';
import 'package:shared_preferences/shared_preferences.dart';

class OrderItem {
  final String name;
  final int qty;

  OrderItem({required this.name, required this.qty});

  Map<String, dynamic> toJson() => {'name': name, 'qty': qty};

  factory OrderItem.fromJson(Map<String, dynamic> json) => OrderItem(
        name: (json['name'] ?? '').toString(),
        qty: (json['qty'] as num).toInt(),
      );
}

class OrderModel {
  final String orderId;
  final DateTime date;
  final List<OrderItem> items;
  final double totalPaid;

  OrderModel({
    required this.orderId,
    required this.date,
    required this.items,
    required this.totalPaid,
  });

  int get totalProducts => items.fold(0, (a, b) => a + b.qty);

  String get itemsText {
    final names = items.map((e) => e.name).toList();
    return names.join(', ');
  }

  Map<String, dynamic> toJson() => {
        'orderId': orderId,
        'date': date.toIso8601String(),
        'items': items.map((e) => e.toJson()).toList(),
        'totalPaid': totalPaid,
      };

  factory OrderModel.fromJson(Map<String, dynamic> json) => OrderModel(
        orderId: (json['orderId'] ?? '').toString(),
        date: DateTime.parse((json['date'] ?? DateTime.now().toIso8601String()).toString()),
        items: ((json['items'] as List?) ?? [])
            .map((e) => OrderItem.fromJson((e as Map).cast<String, dynamic>()))
            .toList(),
        totalPaid: (json['totalPaid'] as num).toDouble(),
      );
}

class OrdersController extends ChangeNotifier {
  static const _key = 'orders_v1';
  final List<OrderModel> _orders = [];

  List<OrderModel> get orders => List.unmodifiable(_orders);

  Future<void> load() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_key);
    if (raw == null || raw.isEmpty) return;

    final list = (jsonDecode(raw) as List).cast<dynamic>();
    _orders
      ..clear()
      ..addAll(list.map((e) => OrderModel.fromJson((e as Map).cast<String, dynamic>())));
    notifyListeners();
  }

  Future<void> _save() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = jsonEncode(_orders.map((o) => o.toJson()).toList());
    await prefs.setString(_key, raw);
  }

  Future<void> addOrder(OrderModel order) async {
    _orders.insert(0, order); // newest first
    await _save();
    notifyListeners();
  }

  Future<void> clear() async {
    _orders.clear();
    await _save();
    notifyListeners();
  }
}

/// Provider sin paquetes
class OrdersScope extends InheritedNotifier<OrdersController> {
  const OrdersScope({
    super.key,
    required OrdersController controller,
    required super.child,
  }) : super(notifier: controller);

  static OrdersController of(context) {
    final scope = context.dependOnInheritedWidgetOfExactType<OrdersScope>();
    if (scope == null) throw Exception('OrdersScope no encontrado. Envuélvelo en main.dart');
    return scope.notifier!;
  }
}
