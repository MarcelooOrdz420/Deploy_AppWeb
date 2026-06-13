class CompanySettings {
  final String brandName;
  final String currency;
  final PaymentChannel yape;
  final PaymentChannel plin;
  final MercadoPagoChannel mercadoPago;
  final CodChannel cod;

  CompanySettings({
    required this.brandName,
    required this.currency,
    required this.yape,
    required this.plin,
    required this.mercadoPago,
    required this.cod,
  });

  factory CompanySettings.fromJson(Map<String, dynamic> json) {
    final payments = (json['payments'] as Map? ?? <String, dynamic>{}).cast<String, dynamic>();
    return CompanySettings(
      brandName: (json['brand_name'] ?? 'Pollos y Parrillas El Dorado').toString(),
      currency: (json['currency'] ?? 'PEN').toString(),
      yape: PaymentChannel.fromJson((payments['yape'] as Map? ?? <String, dynamic>{}).cast<String, dynamic>()),
      plin: PaymentChannel.fromJson((payments['plin'] as Map? ?? <String, dynamic>{}).cast<String, dynamic>()),
      mercadoPago: MercadoPagoChannel.fromJson((payments['mercado_pago'] as Map? ?? <String, dynamic>{}).cast<String, dynamic>()),
      cod: CodChannel.fromJson((payments['cod'] as Map? ?? <String, dynamic>{}).cast<String, dynamic>()),
    );
  }

  static CompanySettings fallback() => CompanySettings(
        brandName: 'Pollos y Parrillas El Dorado',
        currency: 'PEN',
        yape: PaymentChannel(label: 'Yape Empresa', enabled: true),
        plin: PaymentChannel(label: 'Plin Empresa', enabled: true),
        mercadoPago: MercadoPagoChannel(label: 'Mercado Pago', enabled: true),
        cod: CodChannel(label: 'Pago contraentrega', message: 'Pagas cuando recibes tu pedido.', enabled: true),
      );
}

class PaymentChannel {
  final String label;
  final String phone;
  final String qrUrl;
  final bool enabled;

  PaymentChannel({
    required this.label,
    this.phone = '',
    this.qrUrl = '',
    required this.enabled,
  });

  factory PaymentChannel.fromJson(Map<String, dynamic> json) => PaymentChannel(
        label: (json['label'] ?? '').toString(),
        phone: (json['phone'] ?? '').toString(),
        qrUrl: (json['qr_url'] ?? '').toString(),
        enabled: json['enabled'] != false,
      );
}

class MercadoPagoChannel {
  final String label;
  final String publicKey;
  final bool enabled;

  MercadoPagoChannel({
    required this.label,
    this.publicKey = '',
    required this.enabled,
  });

  factory MercadoPagoChannel.fromJson(Map<String, dynamic> json) => MercadoPagoChannel(
        label: (json['label'] ?? '').toString(),
        publicKey: (json['public_key'] ?? '').toString(),
        enabled: json['enabled'] != false,
      );
}

class CodChannel {
  final String label;
  final String message;
  final bool enabled;

  CodChannel({
    required this.label,
    required this.message,
    required this.enabled,
  });

  factory CodChannel.fromJson(Map<String, dynamic> json) => CodChannel(
        label: (json['label'] ?? '').toString(),
        message: (json['message'] ?? '').toString(),
        enabled: json['enabled'] != false,
      );
}
