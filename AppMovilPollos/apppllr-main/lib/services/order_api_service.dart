import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'api_client.dart';

class OrderApiService {
  Future<Map<String, dynamic>> createOrder({
    required String token,
    required Map<String, dynamic> payload,
  }) async {
    try {
      final res = await ApiClient.post<Map<String, dynamic>>(
        '/orders',
        data: payload,
        options: Options(
          headers: {'Authorization': 'Bearer $token'},
        ),
      );

      return (res.data ?? <String, dynamic>{}).cast<String, dynamic>();
    } on DioException catch (e) {
      final data = e.response?.data;
      if (data is Map && data['message'] != null) {
        throw Exception(data['message'].toString());
      }
      if (data is Map && data['errors'] is Map) {
        final values = (data['errors'] as Map).values.toList();
        final firstError = values.isNotEmpty ? values.first : null;
        if (firstError is List && firstError.isNotEmpty) {
          throw Exception(firstError.first.toString());
        }
      }
      throw Exception('No se pudo registrar el pedido');
    }
  }

  Future<List<Map<String, dynamic>>> myOrders() async {
    final prefs = await SharedPreferences.getInstance();
    final token = (prefs.getString('token') ?? '').trim();
    if (token.isEmpty) return <Map<String, dynamic>>[];

    final res = await ApiClient.get<List<dynamic>>(
      '/orders/my',
      options: Options(
        headers: {'Authorization': 'Bearer $token'},
      ),
    );

    final list = (res.data ?? <dynamic>[]);
    return list.map((e) => (e as Map).cast<String, dynamic>()).toList();
  }
}
