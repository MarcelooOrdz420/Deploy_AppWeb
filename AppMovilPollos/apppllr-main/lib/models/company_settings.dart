class CompanySettings {
  final String brandName;
  final String currency;
  final PaymentChannel yape;
  final PaymentChannel plin;
  final TransferChannel transfer;
  final CodChannel cod;

  CompanySettings({
    required this.brandName,
    required this.currency,
    required this.yape,
    required this.plin,
    required this.transfer,
    required this.cod,
  });

  factory CompanySettings.fromJson(Map<String, dynamic> json) {
    final payments = (json['payments'] as Map? ?? <String, dynamic>{}).cast<String, dynamic>();
    return CompanySettings(
      brandName: (json['brand_name'] ?? 'Pollos y Parrillas El Dorado').toString(),
      currency: (json['currency'] ?? 'PEN').toString(),
      yape: PaymentChannel.fromJson((payments['yape'] as Map? ?? <String, dynamic>{}).cast<String, dynamic>()),
      plin: PaymentChannel.fromJson((payments['plin'] as Map? ?? <String, dynamic>{}).cast<String, dynamic>()),
      transfer: TransferChannel.fromJson((payments['transfer'] as Map? ?? <String, dynamic>{}).cast<String, dynamic>()),
      cod: CodChannel.fromJson((payments['cod'] as Map? ?? <String, dynamic>{}).cast<String, dynamic>()),
    );
  }

  static CompanySettings fallback() => CompanySettings(
        brandName: 'Pollos y Parrillas El Dorado',
        currency: 'PEN',
        yape: PaymentChannel(label: 'Yape Empresa', enabled: true),
        plin: PaymentChannel(label: 'Plin Empresa', enabled: true),
        transfer: TransferChannel(label: 'Transferencia bancaria', enabled: true),
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

class TransferChannel {
  final String label;
  final String bankName;
  final String accountNumber;
  final String cci;
  final String accountHolder;
  final bool enabled;

  TransferChannel({
    required this.label,
    this.bankName = '',
    this.accountNumber = '',
    this.cci = '',
    this.accountHolder = '',
    required this.enabled,
  });

  factory TransferChannel.fromJson(Map<String, dynamic> json) => TransferChannel(
        label: (json['label'] ?? '').toString(),
        bankName: (json['bank_name'] ?? '').toString(),
        accountNumber: (json['account_number'] ?? '').toString(),
        cci: (json['cci'] ?? '').toString(),
        accountHolder: (json['account_holder'] ?? '').toString(),
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
