import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:go_router/go_router.dart';

import '../services/location_lookup_service.dart';
import '../services/profile_data_service.dart';
import '../services/session_service.dart';
import '../state/cart_controller.dart';
import '../theme/store_theme.dart';
import '../widgets/producto_image.dart';

class CartTab extends StatefulWidget {
  const CartTab({super.key});

  @override
  State<CartTab> createState() => _CartTabState();
}

class _CartTabState extends State<CartTab> {
  final ProfileDataService _profileDataService = ProfileDataService();
  final LocationLookupService _locationLookupService = LocationLookupService();
  List<SavedAddress> _savedAddresses = const [];

  @override
  void initState() {
    super.initState();
    _loadAddresses();
  }

  Future<void> _loadAddresses() async {
    final logged = await SessionService().isLoggedIn();
    if (!logged) return;
    try {
      final addresses = await _profileDataService.getAddresses();
      if (!mounted) return;
      setState(() => _savedAddresses = addresses);
    } catch (_) {
      if (!mounted) return;
      setState(() => _savedAddresses = const []);
    }
  }

  @override
  Widget build(BuildContext context) {
    final cart = CartScope.of(context);
    final subtotal = cart.subtotal;
    final deliveryFee = cart.deliveryFee(freeOver: 70, fee: cart.isDelivery ? 4 : 0);
    final total = subtotal + deliveryFee;

    return ListView(
      padding: const EdgeInsets.fromLTRB(14, 12, 14, 16),
      children: [
        const StoreSurface(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Carrito',
                style: TextStyle(fontSize: 28, fontWeight: FontWeight.w900),
              ),
              SizedBox(height: 8),
              Text(
                'Revisa tu pedido antes de pasar al pago.',
                style: TextStyle(color: StoreTheme.inkSoft),
              ),
            ],
          ),
        ),
        const SizedBox(height: 14),
        if (cart.items.isEmpty)
          const StoreSurface(
            child: Column(
              children: [
                Icon(Icons.shopping_cart_outlined, size: 48, color: StoreTheme.orange),
                SizedBox(height: 12),
                Text(
                  'Tu carrito esta vacio',
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900),
                ),
              ],
            ),
          ),
        ...cart.items.map((item) {
          return StoreSurface(
            margin: const EdgeInsets.only(bottom: 12),
            child: Column(
              children: [
                Row(
                  children: [
                    ProductoImage(
                      producto: item.producto,
                      width: 72,
                      height: 72,
                      borderRadius: BorderRadius.circular(16),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            item.producto.name,
                            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            'S/ ${item.producto.price.toStringAsFixed(2)}',
                            style: const TextStyle(color: StoreTheme.orangeDeep),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    _qtyButton('-', () => cart.removeOne(item.producto.id)),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 12),
                      child: Text(
                        '${item.qty}',
                        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
                      ),
                    ),
                    _qtyButton('+', () => cart.add(item.producto)),
                    const Spacer(),
                    TextButton(
                      onPressed: () => cart.delete(item.producto.id),
                      child: const Text(
                        'Quitar',
                        style: TextStyle(color: StoreTheme.orangeDeep),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          );
        }),
        if (cart.items.isNotEmpty) ...[
          StoreSurface(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Resumen del pedido',
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900),
                ),
                const SizedBox(height: 10),
                _amountRow('Productos', subtotal),
                _amountRow('Delivery', deliveryFee),
                const Divider(height: 24),
                _amountRow('Total', total, highlight: true),
              ],
            ),
          ),
          const SizedBox(height: 14),
          StoreSurface(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Opciones de entrega',
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900),
                ),
                const SizedBox(height: 14),
                Row(
                  children: [
                    Expanded(
                      child: _choiceButton(
                        label: 'Delivery',
                        active: cart.isDelivery,
                        onTap: () => cart.setDeliveryType(true),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: _choiceButton(
                        label: 'Recojo en local',
                        active: !cart.isDelivery,
                        onTap: () => cart.setDeliveryType(false),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    const Icon(Icons.location_on_outlined, color: StoreTheme.orangeDeep),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        cart.address.isEmpty
                            ? 'Aun no elegiste direccion'
                            : cart.address,
                        style: const TextStyle(color: StoreTheme.inkSoft),
                      ),
                    ),
                    TextButton(
                      onPressed: cart.isDelivery ? () => _showAddressOptions(cart) : null,
                      child: const Text('Cambiar'),
                    ),
                  ],
                ),
                if (cart.reference.isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.only(top: 6),
                    child: Text(
                      'Referencia: ${cart.reference}',
                      style: const TextStyle(color: StoreTheme.inkSoft),
                    ),
                  ),
                const SizedBox(height: 12),
                const Text(
                  'Hora de entrega',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900),
                ),
                const SizedBox(height: 10),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFFF7F0),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: StoreTheme.lineStrong.withOpacity(.82)),
                  ),
                  child: const Text(
                    'Puedes pedir para ahora o programarlo desde 30 minutos hacia adelante. La cocina recibe pedidos hasta las 11:00 PM.',
                    style: TextStyle(
                      color: StoreTheme.inkSoft,
                      fontWeight: FontWeight.w600,
                      height: 1.4,
                    ),
                  ),
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Expanded(
                      child: _radioChoice(
                        label: 'Ahora',
                        active: cart.scheduleNow,
                        onTap: () => cart.setScheduleNow(true),
                      ),
                    ),
                    Expanded(
                      child: _radioChoice(
                        label: 'Programar',
                        active: !cart.scheduleNow,
                        onTap: () => _pickScheduledTime(cart),
                      ),
                    ),
                  ],
                ),
                if (!cart.scheduleNow && cart.scheduledFor != null)
                  Padding(
                    padding: const EdgeInsets.only(top: 12),
                    child: Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFFF7F0),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: StoreTheme.lineStrong.withOpacity(.82)),
                      ),
                      child: Text(
                        cart.deliveryWindowLabel.isNotEmpty
                            ? cart.deliveryWindowLabel
                            : 'Pedido programado para ${_formatSchedule(cart.scheduledFor!)}',
                        style: const TextStyle(
                          color: StoreTheme.inkSoft,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 14),
          FilledButton(
            style: FilledButton.styleFrom(
              backgroundColor: StoreTheme.orange,
              foregroundColor: StoreTheme.ink,
              padding: const EdgeInsets.symmetric(vertical: 16),
            ),
            onPressed: () async {
              final logged = await SessionService().isLoggedIn();
              if (!context.mounted) return;
              if (!logged) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Debes iniciar sesion para continuar con la compra.'),
                  ),
                );
                context.go('/correo');
                return;
              }
              context.push('/pago');
            },
            child: Text('Proceder al pago (${cart.items.length} productos)'),
          ),
        ],
      ],
    );
  }

  Future<void> _showAddressOptions(CartController cart) async {
    await showModalBottomSheet<void>(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (context) {
        return Padding(
          padding: const EdgeInsets.all(12),
          child: StoreSurface(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Direccion de entrega',
                  style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900),
                ),
                const SizedBox(height: 12),
                if (_savedAddresses.isNotEmpty)
                  ..._savedAddresses.map((address) {
                    return ListTile(
                      contentPadding: EdgeInsets.zero,
                      leading: const Icon(Icons.location_on_outlined, color: StoreTheme.orangeDeep),
                      title: Text(address.address),
                      subtitle: address.label == null ? null : Text(address.label!),
                      onTap: () {
                        cart.setAddress(
                          addressValue: address.address,
                          referenceValue: address.label,
                        );
                        Navigator.pop(context);
                      },
                    );
                  }),
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: const Icon(Icons.my_location_outlined, color: StoreTheme.orangeDeep),
                  title: const Text('Usar ubicacion real'),
                  onTap: () async {
                    Navigator.pop(context);
                    await _useCurrentLocation(cart);
                  },
                ),
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: const Icon(Icons.edit_location_alt_outlined, color: StoreTheme.orangeDeep),
                  title: const Text('Escribir direccion manualmente'),
                  onTap: () async {
                    Navigator.pop(context);
                    await _manualAddress(cart);
                  },
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Future<void> _manualAddress(CartController cart) async {
    final addressCtrl = TextEditingController(text: cart.address);
    final referenceCtrl = TextEditingController(text: cart.reference);
    final result = await showDialog<bool>(
      context: context,
      builder: (context) {
        return AlertDialog(
          backgroundColor: StoreTheme.paper,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
          title: const Text('Direccion de entrega'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: addressCtrl,
                decoration: const InputDecoration(labelText: 'Direccion exacta'),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: referenceCtrl,
                decoration: const InputDecoration(labelText: 'Referencia'),
              ),
            ],
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancelar')),
            FilledButton(
              style: FilledButton.styleFrom(
                backgroundColor: StoreTheme.orange,
                foregroundColor: StoreTheme.ink,
              ),
              onPressed: () => Navigator.pop(context, true),
              child: const Text('Guardar'),
            ),
          ],
        );
      },
    );

    if (result == true) {
      cart.setAddress(
        addressValue: addressCtrl.text.trim(),
        referenceValue: referenceCtrl.text.trim(),
      );
    }
  }

  Future<void> _useCurrentLocation(CartController cart) async {
    try {
      final serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        throw Exception('Activa la ubicacion del dispositivo para usar esta opcion.');
      }

      var permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }

      if (permission == LocationPermission.denied || permission == LocationPermission.deniedForever) {
        throw Exception('No diste permiso para acceder a tu ubicacion.');
      }

      final position = await Geolocator.getCurrentPosition();
      final lookup = await _locationLookupService.reverseGeocode(
        latitude: position.latitude,
        longitude: position.longitude,
      );
      cart.setAddress(
        addressValue: lookup.address,
        referenceValue: lookup.reference,
        latitudeValue: position.latitude,
        longitudeValue: position.longitude,
      );

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Ubicacion actual cargada correctamente.')),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString().replaceFirst('Exception: ', ''))),
      );
    }
  }

  Future<void> _pickScheduledTime(CartController cart) async {
    final now = DateTime.now();
    final minimum = now.add(const Duration(minutes: 30));
    final closeTime = DateTime(now.year, now.month, now.day, 23, 0);

    if (!minimum.isBefore(closeTime) && minimum != closeTime) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('La cocina ya no recibe pedidos para hoy. Atendemos hasta las 11:00 PM.'),
        ),
      );
      return;
    }

    final initialTime = TimeOfDay.fromDateTime(minimum);

    final picked = await showTimePicker(
      context: context,
      initialTime: initialTime,
      helpText: 'Programa tu pedido',
    );

    if (picked == null) return;

    final scheduled = DateTime(
      now.year,
      now.month,
      now.day,
      picked.hour,
      picked.minute,
    );

    if (scheduled.isBefore(minimum)) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Debes programar al menos 30 minutos hacia adelante.')),
      );
      return;
    }

    if (scheduled.isAfter(closeTime)) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('La cocina cierra a las 11:00 PM.')),
      );
      return;
    }

    cart.setScheduledFor(
      scheduled,
      label: 'Pedido programado para ${_formatSchedule(scheduled)}',
    );
  }

  String _formatSchedule(DateTime value) {
    final hour = value.hour == 0
        ? 12
        : value.hour > 12
            ? value.hour - 12
            : value.hour;
    final suffix = value.hour >= 12 ? 'PM' : 'AM';
    final minutes = value.minute.toString().padLeft(2, '0');
    return '$hour:$minutes $suffix';
  }

  Widget _amountRow(String label, double value, {bool highlight = false}) {
    final style = TextStyle(
      fontSize: highlight ? 20 : 15,
      fontWeight: highlight ? FontWeight.w900 : FontWeight.w600,
      color: highlight ? StoreTheme.orangeDeep : StoreTheme.ink,
    );

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          Expanded(child: Text(label, style: style)),
          Text('S/ ${value.toStringAsFixed(2)}', style: style),
        ],
      ),
    );
  }

  Widget _qtyButton(String label, VoidCallback onTap) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        width: 36,
        height: 36,
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: const Color(0xFFFFF7F0),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: StoreTheme.lineStrong.withOpacity(.82)),
        ),
        child: Text(
          label,
          style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900),
        ),
      ),
    );
  }

  Widget _choiceButton({
    required String label,
    required bool active,
    required VoidCallback onTap,
  }) {
    return FilledButton(
      style: FilledButton.styleFrom(
        backgroundColor: active ? StoreTheme.orange : Colors.white,
        foregroundColor: StoreTheme.ink,
        side: active ? null : BorderSide(color: StoreTheme.lineStrong.withOpacity(.82)),
      ),
      onPressed: onTap,
      child: Text(label),
    );
  }

  Widget _radioChoice({
    required String label,
    required bool active,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      child: Row(
        children: [
          Icon(
            active ? Icons.radio_button_checked : Icons.radio_button_off,
            color: StoreTheme.orangeDeep,
          ),
          const SizedBox(width: 8),
          Text(label),
        ],
      ),
    );
  }
}
