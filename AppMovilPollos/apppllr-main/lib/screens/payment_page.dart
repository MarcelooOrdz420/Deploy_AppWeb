import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:geolocator/geolocator.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';
import '../models/company_settings.dart';
import '../services/profile_data_service.dart';
import '../services/company_settings_service.dart';
import '../services/location_lookup_service.dart';
import '../services/order_api_service.dart';
import '../services/peru_lookup_service.dart';
import '../services/session_service.dart';
import '../state/cart_controller.dart';
import '../theme/store_theme.dart';

enum PayMethod { izipay }
enum DeliveryType { delivery, pickup }
enum ReceiptType { none, boleta, factura }

class PaymentPage extends StatefulWidget {
  const PaymentPage({super.key});

  @override
  State<PaymentPage> createState() => _PaymentPageState();
}

class _PaymentPageState extends State<PaymentPage> {
  final _orderApiService = OrderApiService();
  final _companySettingsService = CompanySettingsService();
  final _locationLookupService = LocationLookupService();
  final _profileDataService = ProfileDataService();
  final _peruLookupService = PeruLookupService();
  final _sessionService = SessionService();
  final _nameCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _addressCtrl = TextEditingController();
  final _referenceCtrl = TextEditingController();
  final _operationCtrl = TextEditingController();
  final _customerEmailCtrl = TextEditingController();
  final _billingNumberCtrl = TextEditingController();
  final _billingNameCtrl = TextEditingController();
  final _billingEmailCtrl = TextEditingController();
  final _billingAddressCtrl = TextEditingController();
  final _latitudeCtrl = TextEditingController();
  final _longitudeCtrl = TextEditingController();

  PayMethod _method = PayMethod.izipay;
  DeliveryType _deliveryType = DeliveryType.delivery;
  ReceiptType _receiptType = ReceiptType.none;
  String _saladType = 'dulce';
  String _billingDocumentType = '';
  String _lookupMessage = 'Activa boleta o factura para identificar al cliente antes de pagar.';
  String _lastLookupValue = '';
  Map<String, dynamic>? _billingMetadata;
  bool _submitting = false;
  bool _lookingUpDocument = false;
  CompanySettings _settings = CompanySettings.fallback();
  List<SavedAddress> _savedAddresses = const [];
  String? _selectedAddressValue;

  @override
  void initState() {
    super.initState();
    _prefillFromCart();
    _loadSettings();
    _loadSavedAddresses();
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _phoneCtrl.dispose();
    _addressCtrl.dispose();
    _referenceCtrl.dispose();
    _operationCtrl.dispose();
    _customerEmailCtrl.dispose();
    _billingNumberCtrl.dispose();
    _billingNameCtrl.dispose();
    _billingEmailCtrl.dispose();
    _billingAddressCtrl.dispose();
    _latitudeCtrl.dispose();
    _longitudeCtrl.dispose();
    super.dispose();
  }

  bool get _needsOperationCode => false;

  Future<void> _loadSettings() async {
    final settings = await _companySettingsService.fetch();
    if (!mounted) return;
    setState(() => _settings = settings);
  }

  Future<void> _loadSavedAddresses() async {
    final logged = await _sessionService.isLoggedIn();
    if (!logged) return;
    try {
      final addresses = await _profileDataService.getAddresses();
      if (!mounted) return;
      setState(() {
        _savedAddresses = addresses;
        if (_addressCtrl.text.trim().isNotEmpty) {
          for (final item in addresses) {
            if (item.address == _addressCtrl.text.trim()) {
              _selectedAddressValue = item.id.toString();
              break;
            }
          }
        }
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _savedAddresses = const []);
    }
  }

  void _prefillFromCart() {
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      final cart = CartScope.of(context);
      _deliveryType = cart.isDelivery ? DeliveryType.delivery : DeliveryType.pickup;
      _addressCtrl.text = cart.address;
      _referenceCtrl.text = cart.reference;
      if (_nameCtrl.text.trim().isEmpty) _nameCtrl.text = cart.customerName;
      if (_phoneCtrl.text.trim().isEmpty) _phoneCtrl.text = cart.customerPhone;
      if (_customerEmailCtrl.text.trim().isEmpty) _customerEmailCtrl.text = cart.customerEmail;
      if (cart.saladType.isNotEmpty) _saladType = cart.saladType;
      _selectedAddressValue = null;
      if (cart.latitude != null) {
        _latitudeCtrl.text = cart.latitude!.toStringAsFixed(6);
      }
      if (cart.longitude != null) {
        _longitudeCtrl.text = cart.longitude!.toStringAsFixed(6);
      }
      final email = await _sessionService.getUserEmail();
      if (_customerEmailCtrl.text.trim().isEmpty) {
        _customerEmailCtrl.text = email;
      }
      if (mounted) setState(() {});
    });
  }

  String _paymentMethodValue() {
    switch (_method) {
      case PayMethod.izipay:
        return 'izipay';
    }
  }

  double? _parseCoordinate(TextEditingController controller) {
    final value = controller.text.trim();
    if (value.isEmpty) return null;
    return double.tryParse(value);
  }

  String? _receiptValue() {
    switch (_receiptType) {
      case ReceiptType.none:
        return null;
      case ReceiptType.boleta:
        return 'boleta';
      case ReceiptType.factura:
        return 'factura';
    }
  }

  int get _billingDocumentLength => _billingDocumentType == 'ruc' ? 11 : 8;

  String get _billingDocumentLabel => _billingDocumentType == 'ruc' ? 'RUC' : 'DNI';

  Future<void> _lookupDocument({bool silent = false}) async {
    if (_lookingUpDocument) return;

    final token = await _sessionService.getToken();
    if (token.isEmpty) return;

    final number = _billingNumberCtrl.text.trim();
    if (_billingDocumentType.isEmpty || number.isEmpty) {
      if (mounted && !silent) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Selecciona tipo de documento e ingresa el numero.')),
        );
      }
      return;
    }

    if (_billingDocumentType.isNotEmpty && number.length != _billingDocumentLength) {
      if (mounted) {
        setState(() => _lookupMessage = 'El $_billingDocumentLabel debe tener $_billingDocumentLength digitos.');
      }
      return;
    }

    setState(() => _lookingUpDocument = true);

    try {
      final data = _billingDocumentType == 'ruc'
          ? await _peruLookupService.lookupRuc(token: token, ruc: number)
          : await _peruLookupService.lookupDni(token: token, dni: number);
      final normalized = (data['normalized'] as Map? ?? <String, dynamic>{}).cast<String, dynamic>();
      _billingNameCtrl.text = (_billingDocumentType == 'ruc'
              ? normalized['business_name']
              : normalized['full_name'])
          ?.toString() ??
          '';
      if (_nameCtrl.text.trim().isEmpty) {
        _nameCtrl.text = _billingNameCtrl.text.trim();
      }
      _billingAddressCtrl.text = _billingDocumentType == 'ruc'
          ? (normalized['address'] ?? '').toString()
          : '';
      _billingMetadata = {'normalized': normalized};
      _lookupMessage = _billingNameCtrl.text.trim().isEmpty
          ? 'No se encontro informacion para ese $_billingDocumentLabel.'
          : 'Documento identificado. Ya puedes emitir ${_receiptType == ReceiptType.factura ? 'factura' : 'boleta'}.';

      _lastLookupValue = number;

      if (!mounted) return;
      if (!silent) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Documento consultado correctamente.')),
        );
      }
    } catch (e) {
      _lookupMessage = e.toString().replaceFirst('Exception: ', '');
      if (!mounted) return;
      if (!silent) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString().replaceFirst('Exception: ', ''))),
        );
      }
    } finally {
      if (mounted) setState(() => _lookingUpDocument = false);
    }
  }

  void _onReceiptChanged(ReceiptType value) {
    setState(() {
      _receiptType = value;
      if (_receiptType == ReceiptType.none) {
        _billingDocumentType = '';
        _billingNumberCtrl.clear();
        _billingNameCtrl.clear();
        _billingEmailCtrl.clear();
        _billingAddressCtrl.clear();
        _billingMetadata = null;
      } else {
        _billingDocumentType = _receiptType == ReceiptType.factura ? 'ruc' : 'dni';
        _billingNumberCtrl.clear();
        _billingNameCtrl.clear();
        _billingAddressCtrl.clear();
        _billingMetadata = null;
        _lookupMessage = _receiptType == ReceiptType.factura
            ? 'Ingresa el RUC del cliente y consultamos la razon social.'
            : 'Ingresa el DNI del cliente y lo identificamos automaticamente.';
      }
      if (_receiptType == ReceiptType.none) {
        _lookupMessage = 'Activa boleta o factura para identificar al cliente antes de pagar.';
      }
      _lastLookupValue = '';
    });
  }

  void _onDocumentChanged(String value) {
    final digits = value.replaceAll(RegExp(r'[^0-9]'), '');
    if (digits != value) {
      _billingNumberCtrl.value = _billingNumberCtrl.value.copyWith(
        text: digits,
        selection: TextSelection.collapsed(offset: digits.length),
      );
    }

    if (!mounted) return;

    setState(() {
      if (_billingDocumentType.isNotEmpty) {
        _lookupMessage = digits.length < _billingDocumentLength
            ? 'Faltan ${_billingDocumentLength - digits.length} digitos para consultar el $_billingDocumentLabel.'
            : _lookupMessage;
      }
    });

    final requiredLength = _billingDocumentLength;
    if (_billingDocumentType.isNotEmpty &&
        digits.length == requiredLength &&
        digits != _lastLookupValue) {
      _lookupDocument(silent: true);
    }
  }

  Future<void> _submitOrder() async {
    if (_submitting) return;

    final cart = CartScope.of(context);
    if (cart.items.isEmpty) return;

    final token = await _sessionService.getToken();
    if (token.isEmpty) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Inicia sesion para registrar tu pedido.')),
      );
      return;
    }

    final customerName = _nameCtrl.text.trim();
    final customerPhone = _phoneCtrl.text.trim();
    final address = _addressCtrl.text.trim();
    final operationCode = _operationCtrl.text.trim();
    final hasChicken =
        cart.items.any((item) => item.producto.categoria.toLowerCase() == 'pollos');

    if (customerName.isEmpty || customerPhone.isEmpty) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Completa nombre y telefono.')),
      );
      return;
    }

    if (_deliveryType == DeliveryType.delivery && address.isEmpty) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('La direccion es obligatoria para delivery.')),
      );
      return;
    }

    final now = DateTime.now();
    final closeTime = DateTime(now.year, now.month, now.day, 23, 0);
    if (!_isWithinKitchenSchedule(now, closeTime, cart)) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'La cocina recibe pedidos hasta las 11:00 PM y los programados deben ser desde 30 minutos adelante.',
          ),
        ),
      );
      return;
    }

      if (_needsOperationCode && operationCode.isEmpty) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Ingresa el codigo de operacion del pago.')),
      );
      return;
    }

    setState(() => _submitting = true);

    try {
      cart.setDeliveryType(_deliveryType == DeliveryType.delivery);
      if (_deliveryType == DeliveryType.delivery) {
        cart.setAddress(
          addressValue: address,
          referenceValue: _referenceCtrl.text.trim(),
          latitudeValue: _parseCoordinate(_latitudeCtrl),
          longitudeValue: _parseCoordinate(_longitudeCtrl),
        );
      }

      final response = await _orderApiService.createOrder(
        token: token,
        payload: {
          'customer_name': customerName,
          'customer_phone': customerPhone,
          'customer_email': _customerEmailCtrl.text.trim().isEmpty ? null : _customerEmailCtrl.text.trim(),
          'delivery_type': _deliveryType == DeliveryType.delivery ? 'delivery' : 'pickup',
          'scheduled_for': cart.scheduleNow || cart.scheduledFor == null
              ? null
              : cart.scheduledFor!.toIso8601String(),
          'delivery_window_label': cart.scheduleNow ? 'Lo antes posible' : cart.deliveryWindowLabel,
          'payment_method': _paymentMethodValue(),
          'payment_reference': operationCode.isEmpty ? null : operationCode,
          'billing_receipt_type': _receiptValue(),
          'billing_document_type': _billingDocumentType.isEmpty ? null : _billingDocumentType,
          'billing_document_number': _billingNumberCtrl.text.trim().isEmpty ? null : _billingNumberCtrl.text.trim(),
          'billing_name': _billingNameCtrl.text.trim().isEmpty ? null : _billingNameCtrl.text.trim(),
          'billing_email': _billingEmailCtrl.text.trim().isEmpty ? null : _billingEmailCtrl.text.trim(),
          'billing_address': _billingAddressCtrl.text.trim().isEmpty ? null : _billingAddressCtrl.text.trim(),
          'billing_metadata': _billingMetadata,
          'salad_type': hasChicken ? _saladType : null,
          'drink_note': cart.orderNote.trim().isEmpty ? null : cart.orderNote.trim(),
          'address': address.isEmpty ? null : address,
          'reference': _referenceCtrl.text.trim().isEmpty ? null : _referenceCtrl.text.trim(),
          'latitude': _parseCoordinate(_latitudeCtrl),
          'longitude': _parseCoordinate(_longitudeCtrl),
          'items': cart.items
              .map((item) => {
                    'product_id': item.producto.id,
                    'quantity': item.qty,
                  })
              .toList(),
        },
      );

      final trackingCode = (response['tracking_code'] ?? '').toString();
      final orderId = (response['id'] as num?)?.toInt() ?? 0;
      final totalPaid = double.tryParse((response['total_amount'] ?? '0').toString()) ??
          (cart.subtotal +
              (_deliveryType == DeliveryType.delivery ? cart.deliveryFee() : 0.0));
      final itemsText = cart.items.map((item) => item.producto.name).join(', ');

      if (_method == PayMethod.izipay && orderId > 0) {
        final checkout = await _orderApiService.izipayCheckout(
          token: token,
          orderId: orderId,
        );
        final checkoutUrl = (checkout['payment_url'] ?? '').toString().trim();

        if (checkoutUrl.isNotEmpty && mounted) {
          await Clipboard.setData(ClipboardData(text: checkoutUrl));
          final opened = await launchUrl(
            Uri.parse(checkoutUrl),
            mode: LaunchMode.externalApplication,
          );
          if (!opened && mounted) {
            await showDialog<void>(
              context: context,
              builder: (context) => AlertDialog(
                title: const Text('Izipay listo'),
                content: Text(
                  'Copiamos el enlace de pago para tu pedido $trackingCode. '
                  'Abre tu navegador y pega el enlace para completar el pago seguro.',
                ),
                actions: [
                  TextButton(
                    onPressed: () => Navigator.pop(context),
                    child: const Text('Continuar'),
                  ),
                ],
              ),
            );
          }
        }

        if (!mounted) return;
        final verified = await _showPaymentStatusDialog(
          token: token,
          orderId: orderId,
          trackingCode: trackingCode,
          checkoutUrl: checkoutUrl,
        );
        if (!verified) return;
      }

      if (!mounted) return;
      context.push(
        '/confirmacion',
        extra: {
          'trackingCode': trackingCode,
          'totalPaid': totalPaid,
          'itemsText': itemsText,
        },
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString().replaceFirst('Exception: ', ''))),
      );
    } finally {
      if (mounted) {
        setState(() => _submitting = false);
      }
    }
  }

  Future<bool> _showPaymentStatusDialog({
    required String token,
    required int orderId,
    required String trackingCode,
    required String checkoutUrl,
  }) async {
    var status = 'pending';
    var checking = false;
    return await showDialog<bool>(
          context: context,
          barrierDismissible: false,
          builder: (dialogContext) => StatefulBuilder(
            builder: (context, setDialogState) => AlertDialog(
              title: Text(status == 'verified'
                  ? 'Pago confirmado'
                  : status == 'rejected' ? 'Pago rechazado' : 'Pedido creado'),
              content: Text(status == 'verified'
                  ? 'Izipay confirmo el pago del pedido $trackingCode.'
                  : status == 'rejected'
                      ? 'Izipay rechazo el pago. Puedes volver a abrir el checkout.'
                      : 'El pedido $trackingCode fue creado. El pago sigue pendiente hasta que Izipay lo confirme.'),
              actions: [
                if (status != 'verified' && checkoutUrl.isNotEmpty)
                  TextButton(
                    onPressed: () => launchUrl(Uri.parse(checkoutUrl), mode: LaunchMode.externalApplication),
                    child: const Text('Abrir Izipay'),
                  ),
                if (status != 'verified')
                  TextButton(
                    onPressed: checking ? null : () async {
                      setDialogState(() => checking = true);
                      try {
                        final order = await _orderApiService.getOrder(token: token, orderId: orderId);
                        setDialogState(() {
                          status = (order['payment_status'] ?? 'pending').toString();
                          checking = false;
                        });
                      } catch (_) {
                        setDialogState(() => checking = false);
                      }
                    },
                    child: Text(checking ? 'Verificando...' : 'Verificar pago'),
                  ),
                TextButton(
                  onPressed: () => Navigator.pop(dialogContext, status == 'verified'),
                  child: Text(status == 'verified' ? 'Continuar' : 'Revisar despues'),
                ),
              ],
            ),
          ),
        ) ?? false;
  }

  @override
  Widget build(BuildContext context) {
    final cart = CartScope.of(context);
    final hasChicken = cart.items.any((item) => item.producto.categoria.toLowerCase() == 'pollos');
    final deliveryFee = _deliveryType == DeliveryType.delivery ? cart.deliveryFee() : 0.0;
    final total = cart.subtotal + deliveryFee;
    final selectedSavedAddressId = (_selectedAddressValue != null &&
            _savedAddresses.any((item) => item.id.toString() == _selectedAddressValue))
        ? _selectedAddressValue
        : null;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Pago y entrega'),
        backgroundColor: StoreTheme.ink,
        foregroundColor: StoreTheme.cream,
      ),
      bottomNavigationBar: _bottomPayBar(cart: cart, total: total),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(14, 14, 14, 112),
        children: [
          const Text(
            'Completa tus datos y elige como pagar',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 12),
          _section(
            title: 'Datos del cliente',
            child: Column(
              children: [
                _field(_nameCtrl, 'Nombre'),
                const SizedBox(height: 10),
                _field(_phoneCtrl, 'Telefono'),
                const SizedBox(height: 10),
                _field(_customerEmailCtrl, 'Correo (opcional)'),
              ],
            ),
          ),
          _section(title: 'Boleta', child: _billingSection()),
          _section(
            title: 'Entrega',
            child: Column(
              children: [
                SegmentedButton<DeliveryType>(
                  segments: const [
                    ButtonSegment(
                      value: DeliveryType.delivery,
                      label: Text('Delivery'),
                      icon: Icon(Icons.delivery_dining),
                    ),
                    ButtonSegment(
                      value: DeliveryType.pickup,
                      label: Text('Recojo'),
                      icon: Icon(Icons.storefront),
                    ),
                  ],
                  selected: {_deliveryType},
                  onSelectionChanged: (values) {
                    setState(() => _deliveryType = values.first);
                  },
                ),
                if (_deliveryType == DeliveryType.delivery) ...[
                  const SizedBox(height: 10),
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFFF7EF),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: const Color(0xFFFFD4B1)),
                    ),
                    child: Text(
                      cart.scheduleNow
                          ? 'Entrega: lo antes posible. Si prefieres una hora exacta, prográmalo desde el carrito.'
                          : 'Entrega programada: ${cart.deliveryWindowLabel.isEmpty ? _formatSchedule(cart.scheduledFor) : cart.deliveryWindowLabel}',
                      style: const TextStyle(
                        fontSize: 13,
                        color: Colors.black87,
                        height: 1.4,
                      ),
                    ),
                  ),
                  const SizedBox(height: 10),
                  _field(_addressCtrl, 'Direccion de entrega'),
                  const SizedBox(height: 10),
                  _field(_referenceCtrl, 'Referencia (opcional)'),
                  if (_savedAddresses.isNotEmpty) ...[
                    const SizedBox(height: 10),
                    DropdownButtonFormField<String>(
                      decoration: _decor('Direcciones guardadas'),
                      value: selectedSavedAddressId,
                      items: _savedAddresses
                          .map((item) => DropdownMenuItem<String>(
                                value: item.id.toString(),
                                child: Text(item.address),
                              ))
                          .toList(),
                      onChanged: (value) {
                        if (value == null) return;
                        final selectedId = int.tryParse(value);
                        if (selectedId == null) return;

                        SavedAddress? selected;
                        for (final item in _savedAddresses) {
                          if (item.id == selectedId) {
                            selected = item;
                            break;
                          }
                        }
                        if (selected == null) return;
                        final selectedValue = selected.id.toString();
                        final selectedAddress = selected.address;
                        final selectedLabel = selected.label;

                        setState(() => _selectedAddressValue = selectedValue);
                        _addressCtrl.text = selectedAddress;
                        if (selectedLabel != null) {
                          _referenceCtrl.text = selectedLabel;
                        }
                      },
                    ),
                  ],
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      Expanded(child: _field(_latitudeCtrl, 'Latitud (opcional)')),
                      const SizedBox(width: 10),
                      Expanded(child: _field(_longitudeCtrl, 'Longitud (opcional)')),
                    ],
                  ),
                  const SizedBox(height: 10),
                  OutlinedButton.icon(
                    onPressed: _useCurrentLocation,
                    icon: const Icon(Icons.my_location_outlined),
                    label: const Text('Usar ubicacion exacta'),
                  ),
                ],
              ],
            ),
          ),
          if (hasChicken)
            _section(
              title: 'Ensalada para pollos',
              child: DropdownButtonFormField<String>(
                value: _saladType,
                items: const [
                  DropdownMenuItem(value: 'dulce', child: Text('Dulce')),
                  DropdownMenuItem(value: 'salada', child: Text('Salada')),
                ],
                decoration: _decor('Tipo de ensalada'),
                onChanged: (value) => setState(() => _saladType = value ?? 'dulce'),
              ),
            ),
          _section(
            title: 'Metodo de pago',
            child: Column(
              children: [
                _payTile('Izipay', PayMethod.izipay, Icons.credit_card),
                const SizedBox(height: 10),
                _paymentPanel(),
                if (_needsOperationCode) ...[
                  const SizedBox(height: 10),
                  _field(_operationCtrl, 'Codigo de operacion'),
                ],
              ],
            ),
          ),
          _section(
            title: 'Resumen',
            child: Column(
              children: [
                _row('Subtotal', cart.subtotal),
                _row('Delivery', deliveryFee),
                const Divider(),
                _row('Total', total, highlight: true),
              ],
            ),
          ),
          const SizedBox(height: 8),
        ],
      ),
    );
  }

  Widget _bottomPayBar({
    required CartController cart,
    required double total,
  }) {
    return SafeArea(
      top: false,
      child: Container(
        padding: const EdgeInsets.fromLTRB(14, 10, 14, 12),
        decoration: const BoxDecoration(
          color: StoreTheme.ink,
          border: Border(
            top: BorderSide(color: StoreTheme.orangeSoft, width: 1.2),
          ),
          boxShadow: [
            BoxShadow(
              color: Color.fromRGBO(0, 0, 0, .28),
              blurRadius: 20,
              offset: Offset(0, -8),
            ),
          ],
        ),
        child: Row(
          children: [
            Expanded(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Total a pagar',
                    style: TextStyle(
                      color: StoreTheme.orangeSoft,
                      fontSize: 12,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  Text(
                    'S/. ${total.toStringAsFixed(2)}',
                    style: const TextStyle(
                      color: StoreTheme.cream,
                      fontSize: 22,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 12),
            ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: StoreTheme.orange,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14),
                ),
              ),
              onPressed: cart.items.isEmpty || _submitting ? null : _submitOrder,
              icon: _submitting
                  ? const SizedBox(
                      width: 16,
                      height: 16,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Colors.white,
                      ),
                    )
                  : const Icon(Icons.lock_outline),
              label: Text(_submitting ? 'Enviando' : 'Pagar'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _section({required String title, required Widget child}) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
            const SizedBox(height: 10),
            child,
          ],
        ),
      ),
    );
  }

  Widget _field(TextEditingController controller, String label) {
    return TextField(
      controller: controller,
      decoration: _decor(label),
    );
  }

  Future<void> _useCurrentLocation() async {
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
      _latitudeCtrl.text = position.latitude.toStringAsFixed(6);
      _longitudeCtrl.text = position.longitude.toStringAsFixed(6);
      _addressCtrl.text = lookup.address;
      _referenceCtrl.text = lookup.reference;
      _selectedAddressValue = null;

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

  Widget _billingSection() {
    final docLabel = _receiptType == ReceiptType.factura ? 'RUC' : 'DNI';
    final docLength = _receiptType == ReceiptType.factura ? 11 : 8;
    final requiresReceipt = _receiptType != ReceiptType.none;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        DropdownButtonFormField<ReceiptType>(
          value: _receiptType,
          items: const [
            DropdownMenuItem(value: ReceiptType.none, child: Text('No deseo comprobante')),
            DropdownMenuItem(value: ReceiptType.boleta, child: Text('Boleta con DNI')),
            DropdownMenuItem(value: ReceiptType.factura, child: Text('Factura con RUC')),
          ],
          decoration: _decor('Comprobante'),
          onChanged: (value) => _onReceiptChanged(value ?? ReceiptType.none),
        ),
        const SizedBox(height: 12),
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: const Color(0xFFFFF7EF),
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: const Color(0xFFFFD4B1)),
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Icon(Icons.verified_user_outlined, color: Colors.orange),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  _lookupMessage,
                  style: const TextStyle(fontSize: 13, color: Colors.black87),
                ),
              ),
            ],
          ),
        ),
        if (requiresReceipt) ...[
          const SizedBox(height: 12),
          TextField(
            controller: _billingNumberCtrl,
            keyboardType: TextInputType.number,
            inputFormatters: [
              FilteringTextInputFormatter.digitsOnly,
              LengthLimitingTextInputFormatter(docLength),
            ],
            onChanged: _onDocumentChanged,
            decoration: _decor('$docLabel del cliente').copyWith(
              hintText: _receiptType == ReceiptType.factura ? 'Ej: 20131312955' : 'Ej: 12345678',
              suffixIcon: _lookingUpDocument
                  ? const Padding(
                      padding: EdgeInsets.all(12),
                      child: SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      ),
                    )
                  : IconButton(
                      onPressed: () => _lookupDocument(),
                      icon: const Icon(Icons.search),
                    ),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _billingNameCtrl,
            readOnly: true,
            decoration: _decor(_receiptType == ReceiptType.factura ? 'Razon social' : 'Nombre del cliente'),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _billingEmailCtrl,
            keyboardType: TextInputType.emailAddress,
            decoration: _decor('Correo para enviar comprobante'),
          ),
        ],
      ],
    );
  }

  InputDecoration _decor(String label) {
    return InputDecoration(
      labelText: label,
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
      filled: true,
      fillColor: const Color(0xFFFFFBF8),
    );
  }

  Widget _payTile(String title, PayMethod method, IconData icon) {
    return RadioListTile<PayMethod>(
      contentPadding: const EdgeInsets.symmetric(horizontal: 4),
      value: method,
      groupValue: _method,
      onChanged: (value) => setState(() => _method = value!),
      title: Text(
        title,
        maxLines: 2,
        overflow: TextOverflow.ellipsis,
        style: const TextStyle(fontWeight: FontWeight.w700),
      ),
      secondary: Icon(icon, color: Colors.orange),
    );
  }

  Widget _paymentPanel() {
    return _paymentInfoCard(
      title: _settings.izipay.label,
      subtitle: _settings.izipay.message.isEmpty
          ? 'Te llevaremos al checkout seguro de Izipay para pagar con tarjeta.'
          : _settings.izipay.message,
      child: const Icon(Icons.credit_score_outlined, size: 44, color: Colors.orange),
    );
  }

  Widget _paymentInfoCard({
    required String title,
    required String subtitle,
    required Widget child,
  }) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(14),
        color: const Color(0xFFFFF7EF),
        border: Border.all(color: const Color(0xFFFFD4B1)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 4),
          Text(
            subtitle,
            style: const TextStyle(color: Colors.black54, height: 1.35),
            softWrap: true,
          ),
          const SizedBox(height: 10),
          Center(child: child),
        ],
      ),
    );
  }

  Widget _networkPreview(String url) {
    if (url.trim().isEmpty) {
      return Container(
        width: 170,
        height: 170,
        color: Colors.white,
        alignment: Alignment.center,
        child: const Text('QR pendiente\nen backend', textAlign: TextAlign.center),
      );
    }

    return ClipRRect(
      borderRadius: BorderRadius.circular(14),
      child: Image.network(
        url,
        width: 170,
        height: 170,
        fit: BoxFit.contain,
        webHtmlElementStrategy: WebHtmlElementStrategy.prefer,
        errorBuilder: (_, __, ___) => Container(
          width: 170,
          height: 170,
          color: Colors.white,
          alignment: Alignment.center,
          child: const Text('Coloca tu QR\nen /public/images', textAlign: TextAlign.center),
        ),
      ),
    );
  }

  Widget _row(String label, double amount, {bool highlight = false}) {
    final style = TextStyle(
      fontWeight: highlight ? FontWeight.bold : FontWeight.normal,
      color: highlight ? Colors.orange.shade700 : Colors.black87,
    );
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          Expanded(child: Text(label, style: style)),
          Text('S/. ${amount.toStringAsFixed(2)}', style: style),
        ],
      ),
    );
  }

  bool _isWithinKitchenSchedule(DateTime now, DateTime closeTime, CartController cart) {
    if (cart.scheduleNow) {
      return !now.isAfter(closeTime);
    }

    final scheduled = cart.scheduledFor;
    if (scheduled == null) {
      return false;
    }

    final minimum = now.add(const Duration(minutes: 30));
    if (scheduled.isBefore(minimum)) {
      return false;
    }

    return !scheduled.isAfter(closeTime);
  }

  String _formatSchedule(DateTime? value) {
    if (value == null) return 'Hora pendiente';
    final hour = value.hour == 0
        ? 12
        : value.hour > 12
            ? value.hour - 12
            : value.hour;
    final suffix = value.hour >= 12 ? 'PM' : 'AM';
    final minutes = value.minute.toString().padLeft(2, '0');
    return '$hour:$minutes $suffix';
  }
}
