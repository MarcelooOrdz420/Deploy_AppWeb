import 'package:dio/dio.dart';
import 'package:google_sign_in/google_sign_in.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../config/runtime_config.dart';
import 'api_client.dart';
import 'push_notifications_service.dart';

class RegisterResponse {
  RegisterResponse({
    required this.email,
    required this.message,
    required this.requiresVerification,
  });

  final String email;
  final String message;
  final bool requiresVerification;
}

class AuthService {
  Future<void> _persistAuthPayload(Map<String, dynamic> data, {required String fallbackEmail}) async {
    final token = data['token']?.toString();

    if (token == null || token.isEmpty) {
      throw Exception('Token no recibido');
    }

    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('token', token);

    final user = (data['user'] is Map) ? (data['user'] as Map).cast<String, dynamic>() : null;
    await prefs.setInt('user_id', (user?['id'] as num?)?.toInt() ?? 0);
    await prefs.setString('user_name', user?['name']?.toString() ?? '');
    await prefs.setString('user_email', user?['email']?.toString() ?? fallbackEmail);
    await prefs.setString('user_phone', user?['phone']?.toString() ?? '');
    await prefs.setString('user_role', user?['role']?.toString() ?? 'customer');

    await PushNotificationsService.instance.syncOrderTopics();
  }

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
      await _persistAuthPayload(data, fallbackEmail: email);
    } on DioException catch (e) {
      final status = e.response?.statusCode;
      final msg = _messageFromDio(e, fallback: 'No se pudo iniciar sesion');

      if (status == 401) throw Exception(msg);
      if (status == 422) throw Exception(msg);
      throw Exception(status != null ? '($status) $msg' : msg);
    }
  }

  Future<RegisterResponse> register({
    required String email,
    required String password,
    String? name,
    String? phone,
  }) async {
    try {
      final res = await ApiClient.post(
        '/auth/register',
        data: {
          'email': email,
          'password': password,
          'name': (name ?? '').trim(),
          'phone': (phone ?? '').trim().isEmpty ? null : phone!.trim(),
        },
      );

      final data = (res.data as Map).cast<String, dynamic>();

      return RegisterResponse(
        email: email,
        message: data['message']?.toString() ?? 'Codigo OTP enviado.',
        requiresVerification: data['requires_verification'] == true,
      );
    } on DioException catch (e) {
      final status = e.response?.statusCode;
      final msg = _messageFromDio(e, fallback: 'No se pudo registrar');
      throw Exception(status != null ? '($status) $msg' : msg);
    }
  }

  Future<void> verifyOtp({
    required String email,
    required String code,
  }) async {
    try {
      final res = await ApiClient.post(
        '/auth/verify-otp',
        data: {
          'email': email,
          'code': code,
        },
      );

      final data = (res.data as Map).cast<String, dynamic>();
      await _persistAuthPayload(data, fallbackEmail: email);
    } on DioException catch (e) {
      final status = e.response?.statusCode;
      final msg = _messageFromDio(e, fallback: 'No se pudo verificar el codigo');
      throw Exception(status != null ? '($status) $msg' : msg);
    }
  }

  Future<void> resendOtp({
    required String email,
  }) async {
    try {
      await ApiClient.post(
        '/auth/resend-otp',
        data: {
          'email': email,
        },
      );
    } on DioException catch (e) {
      final status = e.response?.statusCode;
      final msg = _messageFromDio(e, fallback: 'No se pudo reenviar el codigo');
      throw Exception(status != null ? '($status) $msg' : msg);
    }
  }

  Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  Future<void> loginWithGoogle() async {
    final serverClientId = RuntimeConfig.googleServerClientId.trim();

    if (serverClientId.isEmpty) {
      throw Exception('Falta configurar google.server_client_id en runtime_config.json');
    }

    final googleSignIn = GoogleSignIn(
      scopes: const ['email', 'profile'],
      serverClientId: serverClientId,
    );

    try {
      final account = await googleSignIn.signIn();

      if (account == null) {
        throw Exception('Inicio de sesion con Google cancelado.');
      }

      final auth = await account.authentication;
      final idToken = auth.idToken;

      if (idToken == null || idToken.isEmpty) {
        throw Exception('Google no entrego el token de identidad.');
      }

      final res = await ApiClient.post(
        '/auth/google',
        data: {'id_token': idToken},
      );

      final data = (res.data as Map).cast<String, dynamic>();
      await _persistAuthPayload(data, fallbackEmail: account.email);
    } on DioException catch (e) {
      final status = e.response?.statusCode;
      final msg = _messageFromDio(e, fallback: 'No se pudo iniciar sesion con Google');
      throw Exception(status != null ? '($status) $msg' : msg);
    }
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
