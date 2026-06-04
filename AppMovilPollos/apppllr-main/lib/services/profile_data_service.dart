import 'package:dio/dio.dart';

import 'api_client.dart';
import 'session_service.dart';

class SavedAddress {
  const SavedAddress({
    required this.id,
    required this.address,
    this.label,
  });

  final int id;
  final String address;
  final String? label;

  factory SavedAddress.fromJson(Map<String, dynamic> json) {
    return SavedAddress(
      id: (json['id'] as num?)?.toInt() ?? 0,
      address: (json['address'] ?? '').toString(),
      label: (json['label'] ?? '').toString().trim().isEmpty
          ? null
          : (json['label'] ?? '').toString(),
    );
  }
}

class ProfileDataService {
  final SessionService _session = SessionService();

  Future<List<SavedAddress>> getAddresses() async {
    final token = await _session.getToken();
    if (token.isEmpty) return <SavedAddress>[];

    final res = await ApiClient.get<List<dynamic>>(
      '/profile/addresses',
      options: Options(
        headers: {'Authorization': 'Bearer $token'},
      ),
    );

    final data = res.data ?? <dynamic>[];
    return data
        .map((item) => SavedAddress.fromJson((item as Map).cast<String, dynamic>()))
        .toList();
  }

  Future<void> addAddress(String value, {String? label}) async {
    final token = await _session.getToken();
    if (token.isEmpty) {
      throw Exception('Debes iniciar sesion para guardar direcciones.');
    }

    await ApiClient.post<Map<String, dynamic>>(
      '/profile/addresses',
      data: {
        'address': value,
        'label': (label ?? '').trim().isEmpty ? null : label!.trim(),
      },
      options: Options(
        headers: {'Authorization': 'Bearer $token'},
      ),
    );
  }

  Future<void> removeAddress(int addressId) async {
    final token = await _session.getToken();
    if (token.isEmpty) {
      throw Exception('Debes iniciar sesion para eliminar direcciones.');
    }

    await ApiClient.delete<Map<String, dynamic>>(
      '/profile/addresses/$addressId',
      options: Options(
        headers: {'Authorization': 'Bearer $token'},
      ),
    );
  }
}
