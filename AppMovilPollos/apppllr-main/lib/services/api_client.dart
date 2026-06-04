import 'package:dio/dio.dart';
import '../config/api_config.dart';

class ApiClient {
  ApiClient._();

  static const Duration _connectTimeout = Duration(seconds: 20);
  static const Duration _receiveTimeout = Duration(seconds: 20);

  static Dio _buildClient(String baseUrl) {
    return Dio(
      BaseOptions(
        baseUrl: baseUrl,
        connectTimeout: _connectTimeout,
        receiveTimeout: _receiveTimeout,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ),
    );
  }

  static final Dio dio = _buildClient(ApiConfig.baseUrl);

  static Future<Response<T>> get<T>(
    String path, {
    Options? options,
    Map<String, dynamic>? queryParameters,
  }) {
    return _withFallback(
      (client) => client.get<T>(
        path,
        options: options,
        queryParameters: queryParameters,
      ),
    );
  }

  static Future<Response<T>> post<T>(
    String path, {
    dynamic data,
    Options? options,
    Map<String, dynamic>? queryParameters,
  }) {
    return _withFallback(
      (client) => client.post<T>(
        path,
        data: data,
        options: options,
        queryParameters: queryParameters,
      ),
    );
  }

  static Future<Response<T>> delete<T>(
    String path, {
    dynamic data,
    Options? options,
    Map<String, dynamic>? queryParameters,
  }) {
    return _withFallback(
      (client) => client.delete<T>(
        path,
        data: data,
        options: options,
        queryParameters: queryParameters,
      ),
    );
  }

  static Future<Response<T>> _withFallback<T>(
    Future<Response<T>> Function(Dio client) request,
  ) async {
    DioException? lastError;

    for (final baseUrl in ApiConfig.baseUrls) {
      final client = baseUrl == dio.options.baseUrl ? dio : _buildClient(baseUrl);
      try {
        final res = await request(client);
        // Fija origin/baseUrl activo para que imágenes/links usen el mismo host/puerto
        // que el request que sí funcionó.
        ApiConfig.setActiveOrigin(ApiConfig.originFromBaseUrl(baseUrl));
        if (dio.options.baseUrl != baseUrl) {
          dio.options.baseUrl = baseUrl;
        }
        return res;
      } on DioException catch (e) {
        lastError = e;
        if (!_shouldTryNext(e)) rethrow;
      }
    }

    throw lastError ??
        DioException(
          requestOptions: RequestOptions(path: ''),
          error: 'No se pudo conectar con la API.',
        );
  }

  static bool _shouldTryNext(DioException error) {
    return error.type == DioExceptionType.connectionTimeout ||
        error.type == DioExceptionType.receiveTimeout ||
        error.type == DioExceptionType.connectionError;
  }
}
