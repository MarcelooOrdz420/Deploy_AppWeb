class CompanySettings {
  final String brandName;
  final String currency;
  final String supportPhone;
  final IzipayChannel izipay;

  CompanySettings({
    required this.brandName,
    required this.currency,
    required this.supportPhone,
    required this.izipay,
  });

  factory CompanySettings.fromJson(Map<String, dynamic> json) {
    final payments = (json['payments'] as Map? ?? <String, dynamic>{})
        .cast<String, dynamic>();
    return CompanySettings(
      brandName: (json['brand_name'] ?? 'Pollos y Parrillas El Dorado')
          .toString(),
      currency: (json['currency'] ?? 'PEN').toString(),
      supportPhone: (json['support_phone'] ?? '964900990').toString(),
      izipay: IzipayChannel.fromJson(
        (payments['izipay'] as Map? ?? <String, dynamic>{})
            .cast<String, dynamic>(),
      ),
    );
  }

  static CompanySettings fallback() => CompanySettings(
    brandName: 'Pollos y Parrillas El Dorado',
    currency: 'PEN',
    supportPhone: '964900990',
    izipay: IzipayChannel(
      label: 'Pago con tarjeta',
      message: 'Paga con tarjeta desde el checkout seguro de Izipay.',
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
