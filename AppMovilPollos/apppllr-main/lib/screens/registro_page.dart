import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../config/runtime_config.dart';
import '../services/auth_service.dart';
import '../theme/store_theme.dart';

class RegistroPage extends StatefulWidget {
  const RegistroPage({super.key});

  @override
  State<RegistroPage> createState() => _RegistroPageState();
}

class _RegistroPageState extends State<RegistroPage> {
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _correoController = TextEditingController();
  final _passwordController = TextEditingController();
  final _otpController = TextEditingController();

  bool _loading = false;
  bool _obscure = true;
  bool _awaitingOtp = false;
  String _pendingEmail = '';
  String _otpHint = '';
  bool get _googleEnabled => RuntimeConfig.googleServerClientId.trim().isNotEmpty;

  bool _isEmailValid(String email) {
    final r = RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$');
    return r.hasMatch(email);
  }

  String _cleanError(Object e) {
    return e.toString().replaceFirst('Exception: ', '').trim();
  }

  Future<void> _doRegister() async {
    final email = _correoController.text.trim();
    final name = _nameController.text.trim().isEmpty
        ? (email.isEmpty ? 'Cliente El Dorado' : email.split('@').first)
        : _nameController.text.trim();
    final pass = _passwordController.text.trim();

    if (email.isEmpty || pass.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Completa correo y contrasena')),
      );
      return;
    }

    if (!_isEmailValid(email)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Correo invalido')),
      );
      return;
    }

    if (pass.length < 6) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('La contrasena debe tener minimo 6 caracteres')),
      );
      return;
    }

    setState(() => _loading = true);
    try {
      final result = await AuthService().register(
        email: email,
        password: pass,
        name: name,
        phone: _phoneController.text.trim(),
      );

      if (!mounted) return;
      setState(() {
        _awaitingOtp = result.requiresVerification;
        _pendingEmail = email;
        _otpHint = result.message;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(result.message)),
      );
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

  Future<void> _doGoogleRegister() async {
    setState(() => _loading = true);
    try {
      await AuthService().loginWithGoogle();
      if (!mounted) return;
      context.go('/app');
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(_cleanError(e))),
      );
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Widget _googleBadge() {
    return Container(
      width: 22,
      height: 22,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: StoreTheme.lineStrong.withOpacity(.85)),
      ),
      child: const Text(
        'G',
        style: TextStyle(
          fontSize: 13,
          fontWeight: FontWeight.w900,
          color: Color(0xFF4285F4),
        ),
      ),
    );
  }

  Future<void> _doVerifyOtp() async {
    final code = _otpController.text.trim();

    if (_pendingEmail.isEmpty || code.length != 6) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Ingresa el codigo OTP de 6 digitos')),
      );
      return;
    }

    setState(() => _loading = true);
    try {
      await AuthService().verifyOtp(email: _pendingEmail, code: code);
      if (!mounted) return;
      context.go('/app');
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(_cleanError(e))),
      );
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _doResendOtp() async {
    if (_pendingEmail.isEmpty) {
      return;
    }

    setState(() => _loading = true);
    try {
      await AuthService().resendOtp(email: _pendingEmail);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Codigo reenviado correctamente')),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(_cleanError(e))),
      );
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _correoController.dispose();
    _passwordController.dispose();
    _otpController.dispose();
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
                                  'Nuevo cliente',
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w900,
                                    letterSpacing: 2.1,
                                    color: Color(0xFF9B5A2C),
                                  ),
                                ),
                                SizedBox(height: 4),
                                Text(
                                  'Registrarse',
                                  style: TextStyle(fontSize: 28, fontWeight: FontWeight.w900),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 18),
                      if (!_awaitingOtp) ...[
                        TextField(
                          controller: _nameController,
                          enabled: !_loading,
                          decoration: const InputDecoration(
                            labelText: 'Nombre',
                            prefixIcon: Icon(Icons.person_outline),
                          ),
                        ),
                        const SizedBox(height: 12),
                        TextField(
                          controller: _phoneController,
                          enabled: !_loading,
                          decoration: const InputDecoration(
                            labelText: 'Telefono',
                            prefixIcon: Icon(Icons.phone_outlined),
                          ),
                        ),
                        const SizedBox(height: 12),
                        TextField(
                          controller: _correoController,
                          enabled: !_loading,
                          keyboardType: TextInputType.emailAddress,
                          decoration: const InputDecoration(
                            labelText: 'Correo',
                            prefixIcon: Icon(Icons.mail_outline),
                          ),
                        ),
                        const SizedBox(height: 12),
                        TextField(
                          controller: _passwordController,
                          enabled: !_loading,
                          obscureText: _obscure,
                          decoration: InputDecoration(
                            labelText: 'Contrasena',
                            prefixIcon: const Icon(Icons.lock_outline),
                            suffixIcon: IconButton(
                              onPressed: _loading ? null : () => setState(() => _obscure = !_obscure),
                              icon: Icon(_obscure ? Icons.visibility : Icons.visibility_off),
                            ),
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
                            onPressed: _loading ? null : _doRegister,
                            child: _loading
                                ? const SizedBox(
                                    height: 18,
                                    width: 18,
                                    child: CircularProgressIndicator(strokeWidth: 2),
                                  )
                                : const Text('Crear cuenta'),
                          ),
                        ),
                        if (_googleEnabled) ...[
                          const SizedBox(height: 12),
                          SizedBox(
                            width: double.infinity,
                            child: OutlinedButton.icon(
                              onPressed: _loading ? null : _doGoogleRegister,
                              icon: _googleBadge(),
                              label: const Text('Continuar con Google'),
                            ),
                          ),
                        ],
                      ] else ...[
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFFF4E8),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: StoreTheme.lineStrong.withOpacity(.7)),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Verifica tu correo',
                                style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
                              ),
                              const SizedBox(height: 8),
                              Text(
                                _otpHint.isEmpty
                                    ? 'Ingresa el codigo de 6 digitos enviado a $_pendingEmail.'
                                    : _otpHint,
                                style: const TextStyle(color: StoreTheme.inkSoft, height: 1.5),
                              ),
                              const SizedBox(height: 12),
                              TextField(
                                controller: _otpController,
                                enabled: !_loading,
                                keyboardType: TextInputType.number,
                                maxLength: 6,
                                decoration: const InputDecoration(
                                  labelText: 'Codigo OTP',
                                  prefixIcon: Icon(Icons.verified_outlined),
                                  counterText: '',
                                ),
                              ),
                              const SizedBox(height: 14),
                              SizedBox(
                                width: double.infinity,
                                child: FilledButton(
                                  style: FilledButton.styleFrom(
                                    backgroundColor: StoreTheme.orange,
                                    foregroundColor: StoreTheme.ink,
                                    padding: const EdgeInsets.symmetric(vertical: 16),
                                  ),
                                  onPressed: _loading ? null : _doVerifyOtp,
                                  child: _loading
                                      ? const SizedBox(
                                          height: 18,
                                          width: 18,
                                          child: CircularProgressIndicator(strokeWidth: 2),
                                        )
                                      : const Text('Verificar codigo'),
                                ),
                              ),
                              const SizedBox(height: 8),
                              Align(
                                alignment: Alignment.center,
                                child: TextButton(
                                  onPressed: _loading ? null : _doResendOtp,
                                  child: const Text('Reenviar codigo'),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                      const SizedBox(height: 10),
                      Center(
                        child: TextButton(
                          onPressed: _loading ? null : () => context.go('/correo'),
                          child: Text(_awaitingOtp ? 'Ya verifique, ir a iniciar sesion' : 'Ya tengo cuenta'),
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
