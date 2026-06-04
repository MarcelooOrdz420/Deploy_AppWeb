import 'dart:convert';
import 'dart:math';

import 'package:shared_preferences/shared_preferences.dart';

class SessionService {
  Future<bool> isLoggedIn() async {
    final prefs = await SharedPreferences.getInstance();
    return (prefs.getString('token') ?? '').isNotEmpty;
  }

  Future<String> getUserName() async {
    final prefs = await SharedPreferences.getInstance();
    final name = (prefs.getString('user_name') ?? '').trim();
    if (name.isNotEmpty) return name;
    final email = (prefs.getString('user_email') ?? '').trim();
    return email.isNotEmpty ? email : 'Invitado';
  }

  Future<String> getUserEmail() async {
    final prefs = await SharedPreferences.getInstance();
    return (prefs.getString('user_email') ?? '').trim();
  }

  Future<String> getUserRole() async {
    final prefs = await SharedPreferences.getInstance();
    return (prefs.getString('user_role') ?? 'customer').trim();
  }

  Future<int> getUserId() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getInt('user_id') ?? 0;
  }

  Future<String> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return (prefs.getString('token') ?? '').trim();
  }

  Future<String> getOrCreateGuestSessionId() async {
    final prefs = await SharedPreferences.getInstance();
    final existing = (prefs.getString('guest_session') ?? '').trim();
    if (existing.isNotEmpty) return existing;

    final bytes = List<int>.generate(16, (_) => Random.secure().nextInt(256));
    final id = base64UrlEncode(bytes).replaceAll('=', '');
    await prefs.setString('guest_session', id);
    return id;
  }
}
