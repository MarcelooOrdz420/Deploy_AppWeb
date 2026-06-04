import 'package:dio/dio.dart';

import 'api_client.dart';
import 'session_service.dart';

class ChatbotApiService {
  ChatbotApiService({SessionService? session}) : _session = session ?? SessionService();

  final SessionService _session;

  Future<Map<String, dynamic>> sendMessage(String message) async {
    final token = await _session.getToken();

    final payload = <String, dynamic>{
      'message': message,
    };

    Options? options;
    if (token.isNotEmpty) {
      options = Options(headers: {'Authorization': 'Bearer $token'});
    } else {
      payload['guest_session'] = await _session.getOrCreateGuestSessionId();
    }

    final res = await ApiClient.post<Map<String, dynamic>>(
      '/chatbot/message',
      data: payload,
      options: options,
    );

    return (res.data ?? <String, dynamic>{}).cast<String, dynamic>();
  }
}

