import 'package:dio/dio.dart';
import 'api_client.dart';

class PeruLookupService {
  Future<Map<String, dynamic>> lookupDni({
    required String token,
    required String dni,
  }) async {
    try {
      final res = await ApiClient.post<Map<String, dynamic>>(
        '/lookups/dni',
        data: {'dni': dni},
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      return (res.data ?? <String, dynamic>{}).cast<String, dynamic>();
    } on DioException catch (e) {
      final data = e.response?.data;
      if (data is Map && data['message'] != null) {
        throw Exception(data['message'].toString());
      }
      throw Exception('No se pudo consultar el DNI');
    }
  }

  Future<Map<String, dynamic>> lookupRuc({
    required String token,
    required String ruc,
  }) async {
    try {
      final res = await ApiClient.post<Map<String, dynamic>>(
        '/lookups/ruc',
        data: {'ruc': ruc},
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      return (res.data ?? <String, dynamic>{}).cast<String, dynamic>();
    } on DioException catch (e) {
      final data = e.response?.data;
      if (data is Map && data['message'] != null) {
        throw Exception(data['message'].toString());
      }
      throw Exception('No se pudo consultar el RUC');
    }
  }
}
