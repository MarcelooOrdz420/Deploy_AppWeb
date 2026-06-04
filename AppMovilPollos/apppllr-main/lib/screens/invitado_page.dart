import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:shared_preferences/shared_preferences.dart';

class InvitadoPage extends StatefulWidget {
  const InvitadoPage({super.key});

  @override
  State<InvitadoPage> createState() => _InvitadoPageState();
}

class _InvitadoPageState extends State<InvitadoPage> {

  @override
  void initState() {
    super.initState();
    _enterAsGuest();
  }

  Future<void> _enterAsGuest() async {
    final prefs = await SharedPreferences.getInstance();

    // 🔥 Invitado NO tiene token
    await prefs.remove('token');

    await prefs.setString('user_name', 'Invitado');
    await prefs.setString('user_email', '');

    if (!mounted) return;
    context.go('/app');
  }

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      body: Center(
        child: CircularProgressIndicator(),
      ),
    );
  }
}
