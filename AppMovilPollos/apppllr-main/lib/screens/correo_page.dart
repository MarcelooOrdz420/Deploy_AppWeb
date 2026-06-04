import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../services/auth_service.dart';
import '../theme/store_theme.dart';

class LoginCorreoPage extends StatefulWidget {
  const LoginCorreoPage({super.key});

  @override
  State<LoginCorreoPage> createState() => _LoginCorreoPageState();
}

class _LoginCorreoPageState extends State<LoginCorreoPage> {
  final emailController = TextEditingController();
  final passwordController = TextEditingController();
  bool _loading = false;

  String _cleanError(Object e) => e.toString().replaceFirst('Exception: ', '').trim();

  Future<void> _doLogin() async {
    final email = emailController.text.trim();
    final password = passwordController.text;

    if (email.isEmpty || password.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Completa correo y contrasena')),
      );
      return;
    }

    setState(() => _loading = true);
    try {
      await AuthService().login(email: email, password: password);
      if (!mounted) return;
      context.go('/app');
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(_cleanError(e)),
          action: SnackBarAction(
            label: 'Servidor',
            onPressed: () => context.go('/config'),
          ),
        ),
      );
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  void dispose() {
    emailController.dispose();
    passwordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return StoreBackdrop(
      child: Scaffold(
        body: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(14),
              child: StoreFrame(
                child: Padding(
                  padding: const EdgeInsets.all(18),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      IconButton(
                        onPressed: _loading ? null : () => context.go('/'),
                        icon: const Icon(Icons.arrow_back),
                      ),
                      const SizedBox(height: 6),
                      Row(
                        children: [
                          Container(
                            width: 56,
                            height: 56,
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(18),
                              border: Border.all(color: StoreTheme.lineStrong.withOpacity(.88)),
                              gradient: LinearGradient(
                                colors: [
                                  Colors.white.withOpacity(.94),
                                  const Color(0xFFFFF1E3),
                                ],
                              ),
                            ),
                            child: Image.asset('assets/polloia.png'),
                          ),
                          const SizedBox(width: 12),
                          const Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Ingreso de cliente',
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w900,
                                    letterSpacing: 2.1,
                                    color: Color(0xFF9B5A2C),
                                  ),
                                ),
                                SizedBox(height: 4),
                                Text(
                                  'Iniciar sesion',
                                  style: TextStyle(fontSize: 28, fontWeight: FontWeight.w900),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 18),
                      const Text(
                        'Ingresa con tu correo para comprar y seguir tus pedidos.',
                        style: TextStyle(color: StoreTheme.inkSoft, height: 1.5),
                      ),
                      const SizedBox(height: 18),
                      TextField(
                        controller: emailController,
                        enabled: !_loading,
                        keyboardType: TextInputType.emailAddress,
                        decoration: const InputDecoration(
                          labelText: 'Correo',
                          prefixIcon: Icon(Icons.mail_outline),
                        ),
                      ),
                      const SizedBox(height: 12),
                      TextField(
                        controller: passwordController,
                        obscureText: true,
                        enabled: !_loading,
                        onSubmitted: (_) => _loading ? null : _doLogin(),
                        decoration: const InputDecoration(
                          labelText: 'Contrasena',
                          prefixIcon: Icon(Icons.lock_outline),
                        ),
                      ),
                      const SizedBox(height: 18),
                      SizedBox(
                        width: double.infinity,
                        child: FilledButton(
                          style: FilledButton.styleFrom(
                            backgroundColor: StoreTheme.orange,
                            foregroundColor: StoreTheme.ink,
                            padding: const EdgeInsets.symmetric(vertical: 16),
                          ),
                          onPressed: _loading ? null : _doLogin,
                          child: _loading
                              ? const SizedBox(
                                  height: 18,
                                  width: 18,
                                  child: CircularProgressIndicator(strokeWidth: 2),
                                )
                              : const Text('Iniciar sesion'),
                        ),
                      ),
                      const SizedBox(height: 10),
                      Center(
                        child: TextButton(
                          onPressed: _loading ? null : () => context.go('/registro'),
                          child: const Text('No tengo cuenta, registrarme'),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
