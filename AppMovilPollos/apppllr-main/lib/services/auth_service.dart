import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'api_client.dart';
import 'push_notifications_service.dart';

class AuthService {
  String _messageFromDio(DioException e, {String fallback = 'Error de servidor'}) {
    final data = e.response?.data;
    if (data is Map && data['message'] != null) return data['message'].toString();
    if (data is Map && data['errors'] is Map) {
      final errors = (data['errors'] as Map).values.toList();
      final firstError = errors.isNotEmpty ? errors.first : null;
      if (firstError is List && firstError.isNotEmpty) return firstError.first.toString();
    }
    return fallback;
  }

  Future<void> login({
    required String email,
    required String password,
  }) async {
    try {
      final res = await ApiClient.post(
        '/auth/login',
        data: {'email': email, 'password': password},
      );

      final data = (res.data as Map).cast<String, dynamic>();
      final token = data['token']?.toString();

      if (token == null || token.isEmpty) {
        throw Exception('Token no recibido');
      }

      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('token', token);

      final user = (data['user'] is Map) ? (data['user'] as Map).cast<String, dynamic>() : null;
      await prefs.setInt('user_id', (user?['id'] as num?)?.toInt() ?? 0);
      await prefs.setString('user_name', user?['name']?.toString() ?? '');
      await prefs.setString('user_email', user?['email']?.toString() ?? email);
      await prefs.setString('user_phone', user?['phone']?.toString() ?? '');
      await prefs.setString('user_role', user?['role']?.toString() ?? 'customer');

      // Refresca topics FCM por usuario para notificaciones de pedidos.
      await PushNotificationsService.instance.syncOrderTopics();
    } on DioException catch (e) {
      final status = e.response?.statusCode;
      final msg = _messageFromDio(e, fallback: 'No se pudo iniciar sesion');

      if (status == 401) throw Exception('Credenciales incorrectas');
      if (status == 422) throw Exception(msg);
      throw Exception(status != null ? '($status) $msg' : msg);
    }
  }

  Future<void> register({
    required String email,
    required String password,
    String? name,
    String? phone,
  }) async {
    try {
      await ApiClient.post(
        '/auth/register',
        data: {
          'email': email,
          'password': password,
          'name': (name ?? '').trim(),
          'phone': (phone ?? '').trim().isEmpty ? null : phone!.trim(),
        },
      );
    } on DioException catch (e) {
      final status = e.response?.statusCode;
      final msg = _messageFromDio(e, fallback: 'No se pudo registrar');
      throw Exception(status != null ? '($status) $msg' : msg);
    }
  }

  Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
    await prefs.remove('user_id');
    await prefs.remove('user_name');
    await prefs.remove('user_email');
    await prefs.remove('user_phone');
    await prefs.remove('user_role');
    await PushNotificationsService.instance.syncOrderTopics();
  }
}
