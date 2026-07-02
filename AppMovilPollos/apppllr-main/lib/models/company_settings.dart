class CompanySettings {
  final String brandName;
  final String currency;
  final IzipayChannel izipay;
  final IzipayChannel yape;
  final IzipayChannel plin;

  CompanySettings({
    required this.brandName,
    required this.currency,
    required this.izipay,
    required this.yape,
    required this.plin,
  });

  factory CompanySettings.fromJson(Map<String, dynamic> json) {
    final payments = (json['payments'] as Map? ?? <String, dynamic>{}).cast<String, dynamic>();
    return CompanySettings(
      brandName: (json['brand_name'] ?? 'Pollos y Parrillas El Dorado').toString(),
      currency: (json['currency'] ?? 'PEN').toString(),
      izipay: IzipayChannel.fromJson((payments['izipay'] as Map? ?? <String, dynamic>{}).cast<String, dynamic>()),
      yape: IzipayChannel.fromJson((payments['yape'] as Map? ?? <String, dynamic>{}).cast<String, dynamic>()),
      plin: IzipayChannel.fromJson((payments['plin'] as Map? ?? <String, dynamic>{}).cast<String, dynamic>()),
    );
  }

  static CompanySettings fallback() => CompanySettings(
        brandName: 'Pollos y Parrillas El Dorado',
        currency: 'PEN',
        izipay: IzipayChannel(
          label: 'Pago seguro con Izipay',
          message: 'Paga con tarjeta desde el checkout seguro de Izipay.',
          enabled: true,
        ),
        yape: IzipayChannel(
          label: 'Yape con Izipay',
          message: 'Elige Yape en el checkout seguro de Izipay y confirma el pago desde tu app.',
          enabled: true,
        ),
        plin: IzipayChannel(
          label: 'Plin con Izipay',
          message: 'Elige Plin en el checkout seguro de Izipay y completa el pago en tu billetera.',
          enabled: true,
        ),
      );
}

class IzipayChannel {
  final String label;
  final String message;
  final String publicKey;
  final bool enabled;

  IzipayChannel({
    required this.label,
    required this.message,
    this.publicKey = '',
    required this.enabled,
  });

  factory IzipayChannel.fromJson(Map<String, dynamic> json) => IzipayChannel(
        label: (json['label'] ?? '').toString(),
        message: (json['message'] ?? '').toString(),
        publicKey: (json['public_key'] ?? '').toString(),
        enabled: json['enabled'] != false,
      );
}
