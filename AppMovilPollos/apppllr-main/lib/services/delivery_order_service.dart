import 'package:dio/dio.dart';
import 'api_client.dart';

class DeliveryOrderService {
  Future<List<Map<String, dynamic>>> pool({required String token}) async {
    final res = await ApiClient.get<List<dynamic>>(
      '/delivery/orders/pool',
      options: Options(headers: {'Authorization': 'Bearer $token'}),
    );
    final list = res.data ?? <dynamic>[];
    return list.map((e) => (e as Map).cast<String, dynamic>()).toList();
  }

  Future<List<Map<String, dynamic>>> mine({required String token}) async {
    final res = await ApiClient.get<List<dynamic>>(
      '/delivery/orders/mine',
      options: Options(headers: {'Authorization': 'Bearer $token'}),
    );
    final list = res.data ?? <dynamic>[];
    return list.map((e) => (e as Map).cast<String, dynamic>()).toList();
  }

  /// Lanza una Exception con el mensaje del servidor si el pedido ya no
  /// esta disponible (otro repartidor se lo llevo primero).
  Future<Map<String, dynamic>> claim({
    required String token,
    required int orderId,
  }) async {
    try {
      final res = await ApiClient.post<Map<String, dynamic>>(
        '/delivery/orders/$orderId/claim',
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      return (res.data ?? <String, dynamic>{}).cast<String, dynamic>();
    } on DioException catch (e) {
      final data = e.response?.data;
      if (data is Map && data['message'] != null) {
        throw Exception(data['message'].toString());
      }
      throw Exception('No se pudo tomar el pedido.');
    }
  }

  Future<Map<String, dynamic>> markDelivered({
    required String token,
    required int orderId,
  }) async {
    try {
      final res = await ApiClient.patch<Map<String, dynamic>>(
        '/delivery/orders/$orderId/status',
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      return (res.data ?? <String, dynamic>{}).cast<String, dynamic>();
    } on DioException catch (e) {
      final data = e.response?.data;
      if (data is Map && data['message'] != null) {
        throw Exception(data['message'].toString());
      }
      throw Exception('No se pudo marcar como entregado.');
    }
  }
}
