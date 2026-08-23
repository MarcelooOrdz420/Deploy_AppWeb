import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:geolocator/geolocator.dart';
import 'package:go_router/go_router.dart';

import '../config/pusher_config.dart';
import '../services/chatbot_api_service.dart';
import '../services/location_lookup_service.dart';
import '../services/pusher_service.dart';
import '../services/session_service.dart';
import '../services/productos_service.dart';
import '../models/producto.dart';
import '../state/cart_controller.dart';
import '../theme/store_theme.dart';

class ChatBotPage extends StatefulWidget {
  const ChatBotPage({super.key});

  @override
  State<ChatBotPage> createState() => _ChatBotPageState();
}

class _ChatBotPageState extends State<ChatBotPage> {
  static const bool _showDiagnostics = bool.fromEnvironment(
    'SHOW_DIAGNOSTICS',
    defaultValue: false,
  );

  final List<_ChatMessage> _messages = [
    const _ChatMessage(
      role: _ChatRole.bot,
      text:
          'Hola, soy POLL-IA. Puedo ayudarte con dudas sobre productos, pedidos, pagos y delivery. ¿En qué puedo ayudarte hoy?',
      suggestions: ['Ayúdame a comprar', 'Productos', 'Pedidos', 'Delivery'],
    ),
  ];

  final TextEditingController _controller = TextEditingController();
  final ScrollController _scrollController = ScrollController();

  final SessionService _session = SessionService();
  final ChatbotApiService _api = ChatbotApiService();
  final PusherService _pusher = PusherService.instance;

  StreamSubscription<PusherMessage>? _pusherSub;
  String? _chatChannel;
  Timer? _replyFallbackTimer;
  bool _waitingReply = false;

  void _refreshBanner() {
    if (!mounted) return;
    setState(() {});
  }

  @override
  void initState() {
    super.initState();
    _pusher.connectionState.addListener(_refreshBanner);
    _pusher.lastError.addListener(_refreshBanner);
    _pusher.lastEvent.addListener(_refreshBanner);
    _pusher.lastMessageText.addListener(_refreshBanner);
    _pusher.subscribedChannels.addListener(_refreshBanner);
    _initRealtime();
  }

  @override
  void dispose() {
    _pusherSub?.cancel();
    _replyFallbackTimer?.cancel();
    _pusher.connectionState.removeListener(_refreshBanner);
    _pusher.lastError.removeListener(_refreshBanner);
    _pusher.lastEvent.removeListener(_refreshBanner);
    _pusher.lastMessageText.removeListener(_refreshBanner);
    _pusher.subscribedChannels.removeListener(_refreshBanner);
    _controller.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _initRealtime() async {
    try {
      if (!_pusher.isConfigured) {
        print(
          'CHAT: Pusher no configurado (revisa assets/runtime_config.json).',
        );
      }

      final logged = await _session.isLoggedIn();
      final userId = await _session.getUserId();

      if (logged && userId > 0) {
        _chatChannel = PusherConfig.userChannel(userId);
        await _pusher.syncSubscriptions();
      } else {
        final guestId = await _session.getOrCreateGuestSessionId();
        _chatChannel = 'chat-guest.$guestId';
        await _pusher.subscribeToChannel(_chatChannel!);
      }

      if (mounted) setState(() {});
      print(
        'CHAT channel activo: $_chatChannel (logged=$logged userId=$userId)',
      );

      // Canal global de pruebas / notificaciones (por defecto "mi-canal").
      await _pusher.subscribeToChannel(PusherConfig.notificationsChannel);
      if (mounted) setState(() {});

      _pusherSub = _pusher.messages.listen((event) {
        final channel = _chatChannel;
        print(
          'PUSHER EVENT recv: channel=${event.channel} event=${event.name} data=${event.data}',
        );

        if (channel == null) return;
        if (event.channel != channel &&
            event.channel != PusherConfig.notificationsChannel)
          return;

        if (event.name != 'chatbot.reply' && event.name != 'chat.message')
          return;

        final text =
            (event.data['text'] ??
                    event.data['message'] ??
                    event.data['mensaje'] ??
                    event.data['body'] ??
                    event.data['title'] ??
                    event.message)
                .toString()
                .trim();
        if (text.isEmpty) return;

        _addBotMessage(text);
      });
    } catch (_) {
      // Si Pusher no está configurado o falla, igual podremos responder por HTTP.
    }
  }

  Future<void> _sendText([String? preset]) async {
    final text = (preset ?? _controller.text).trim();
    if (text.isEmpty) return;
    final normalized = text.toLowerCase().trim();
    if ([
      'ayúdame a comprar',
      'ayudame a comprar',
      'quiero hacer un pedido',
      'quiero comprar',
      'ayúdame con mi pedido',
      'deseo ordenar',
      'quiero pedir comida',
    ].contains(normalized)) {
      _controller.clear();
      await showModalBottomSheet<void>(
        context: context,
        isScrollControlled: true,
        useSafeArea: true,
        builder: (_) => const _GuidedPurchaseSheet(),
      );
      return;
    }

    setState(() {
      _messages.add(_ChatMessage(role: _ChatRole.user, text: text));
      _messages.add(
        const _ChatMessage(
          role: _ChatRole.bot,
          text: 'Escribiendo...',
          isTyping: true,
        ),
      );
      _waitingReply = true;
    });
    _controller.clear();
    _scrollToBottom();

    try {
      final res = await _api.sendMessage(text);
      final reply = (res['reply'] ?? '').toString().trim();
      final serverChannel = (res['channel'] ?? '').toString().trim();

      if (!mounted) return;

      if (serverChannel.isNotEmpty && serverChannel != _chatChannel) {
        _chatChannel = serverChannel;
        await _pusher.subscribeToChannel(serverChannel);
        print('CHAT channel actualizado por API: $_chatChannel');
      }

      if (reply.isEmpty) {
        _addBotMessage('No pude responder en este momento.');
        return;
      }

      // Si no hay Pusher, mostramos la respuesta directa del API.
      if (!_pusher.isConfigured) {
        _addBotMessage(reply);
        return;
      }

      // Si Pusher está configurado pero el evento no llega, hacemos fallback.
      _replyFallbackTimer?.cancel();
      _replyFallbackTimer = Timer(const Duration(seconds: 5), () {
        if (!mounted) return;
        if (!_waitingReply) return;
        _addBotMessage(reply);
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _waitingReply = false;
        _messages.removeWhere((m) => m.isTyping);
        _messages.add(
          const _ChatMessage(
            role: _ChatRole.bot,
            text:
                'No pude enviar tu mensaje. Revisa tu conexión e inténtalo de nuevo.',
          ),
        );
      });
      _scrollToBottom();
    }
  }

  void _addBotMessage(String text) {
    if (!mounted) return;
    _replyFallbackTimer?.cancel();

    setState(() {
      _waitingReply = false;
      _messages.removeWhere((m) => m.isTyping);

      final last = _messages.isNotEmpty ? _messages.last : null;
      if (last != null &&
          last.role == _ChatRole.bot &&
          !last.isTyping &&
          last.text.trim() == text.trim()) {
        return;
      }

      _messages.add(_ChatMessage(role: _ChatRole.bot, text: text));
    });
    _scrollToBottom();
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!_scrollController.hasClients) return;
      _scrollController.animateTo(
        _scrollController.position.maxScrollExtent + 80,
        duration: const Duration(milliseconds: 220),
        curve: Curves.easeOut,
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        toolbarHeight: 76,
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('POLL-IA'),
            Text(
              'Chat con El Dorado · Asistente de compras',
              style: TextStyle(fontSize: 12),
            ),
          ],
        ),
        backgroundColor: Colors.white,
        foregroundColor: StoreTheme.ink,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => context.pop(),
        ),
      ),
      body: Column(
        children: [
          if (_showDiagnostics)
            Padding(
              padding: const EdgeInsets.fromLTRB(12, 10, 12, 0),
              child: DecoratedBox(
                decoration: BoxDecoration(
                  color: Colors.black.withOpacity(.04),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 10,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _pusher.isConfigured
                            ? 'Realtime: ON (${_pusher.connectionState.value}) • canal: ${_chatChannel ?? '-'} • global: ${PusherConfig.notificationsChannel}'
                            : 'Realtime: OFF (${PusherConfig.notConfiguredReason}) • canal: ${_chatChannel ?? '-'} • global: ${PusherConfig.notificationsChannel}',
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontSize: 12,
                          color: Colors.black54,
                        ),
                      ),
                      if (_pusher.isConfigured) ...[
                        const SizedBox(height: 6),
                        Text(
                          'Subs: ${_pusher.subscribedChannels.value.join(', ')}',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontSize: 11,
                            color: Colors.black45,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Last: ${_pusher.lastEvent.value ?? '-'}${_pusher.lastError.value != null ? ' • ERR: ${_pusher.lastError.value}' : ''}',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontSize: 11,
                            color: Colors.black45,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Text: ${_pusher.lastMessageText.value ?? '-'}',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontSize: 11,
                            color: Colors.black45,
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              ),
            ),
          Expanded(
            child: ListView.builder(
              controller: _scrollController,
              padding: const EdgeInsets.all(12),
              itemCount: _messages.length,
              itemBuilder: (context, index) {
                final msg = _messages[index];
                final isUser = msg.role == _ChatRole.user;

                return Align(
                  alignment: isUser
                      ? Alignment.centerRight
                      : Alignment.centerLeft,
                  child: Container(
                    margin: const EdgeInsets.symmetric(vertical: 6),
                    padding: const EdgeInsets.all(12),
                    constraints: BoxConstraints(
                      maxWidth: MediaQuery.of(context).size.width * 0.82,
                    ),
                    decoration: BoxDecoration(
                      color: isUser ? StoreTheme.orange : Colors.white,
                      borderRadius: BorderRadius.only(
                        topLeft: const Radius.circular(24),
                        topRight: const Radius.circular(24),
                        bottomLeft: Radius.circular(isUser ? 24 : 6),
                        bottomRight: Radius.circular(isUser ? 6 : 24),
                      ),
                      border: isUser
                          ? null
                          : Border.all(color: StoreTheme.borderSoft),
                      boxShadow: const [
                        BoxShadow(
                          color: Color.fromRGBO(25, 22, 20, .05),
                          blurRadius: 16,
                          offset: Offset(0, 6),
                        ),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: isUser
                          ? CrossAxisAlignment.end
                          : CrossAxisAlignment.start,
                      children: [
                        _buildMessageBody(msg, isUser),
                        if (!isUser && msg.suggestions.isNotEmpty) ...[
                          const SizedBox(height: 10),
                          Wrap(
                            spacing: 8,
                            runSpacing: 8,
                            children: msg.suggestions.map((item) {
                              return ActionChip(
                                label: Text(item),
                                onPressed: () => _sendText(item),
                                backgroundColor: Colors.white,
                                side: const BorderSide(
                                  color: Color(0xFFFFC58F),
                                ),
                              );
                            }).toList(),
                          ),
                        ],
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
          SafeArea(
            top: false,
            child: Padding(
              padding: const EdgeInsets.fromLTRB(8, 8, 8, 10),
              child: Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _controller,
                      onSubmitted: (_) => _sendText(),
                      decoration: InputDecoration(
                        hintText: 'Escribe tu mensaje...',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.send, color: Colors.orange),
                    onPressed: _sendText,
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMessageBody(_ChatMessage msg, bool isUser) {
    final baseColor = isUser
        ? Colors.white
        : (msg.isTyping ? Colors.black45 : Colors.black87);
    final baseStyle = TextStyle(
      color: baseColor,
      height: 1.35,
      fontStyle: msg.isTyping ? FontStyle.italic : FontStyle.normal,
    );

    if (isUser || msg.isTyping) {
      return Text(msg.text, style: baseStyle);
    }

    final sections = _parseChatSections(msg.text);
    if (sections.isEmpty) {
      return Text(msg.text, style: baseStyle);
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        for (final section in sections)
          Container(
            width: double.infinity,
            margin: const EdgeInsets.only(bottom: 8),
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: StoreTheme.creamStrong,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: StoreTheme.borderLight),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (section.title != null) ...[
                  Text(
                    section.title!,
                    style: const TextStyle(
                      color: StoreTheme.orangeDark,
                      fontWeight: FontWeight.w800,
                      fontSize: 12.5,
                      letterSpacing: .2,
                    ),
                  ),
                  const SizedBox(height: 6),
                ],
                for (final line in section.lines)
                  Padding(
                    padding: const EdgeInsets.only(bottom: 3),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('•  ', style: baseStyle),
                        Expanded(child: Text(line, style: baseStyle)),
                      ],
                    ),
                  ),
              ],
            ),
          ),
      ],
    );
  }
}

class _GuidedPurchaseSheet extends StatefulWidget {
  const _GuidedPurchaseSheet();
  @override
  State<_GuidedPurchaseSheet> createState() => _GuidedPurchaseSheetState();
}

class _GuidedPurchaseSheetState extends State<_GuidedPurchaseSheet> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController(),
      _phone = TextEditingController(),
      _email = TextEditingController(),
      _address = TextEditingController(),
      _reference = TextEditingController(),
      _notes = TextEditingController();
  final _locationLookupService = LocationLookupService();
  late final Future<List<Producto>> _products = ProductosService().listar();
  int _step = 0, _qty = 1, _drinkQty = 1;
  Producto? _dish, _drink, _side;
  String _salad = '', _delivery = 'delivery';
  @override
  void initState() {
    super.initState();
    _prefillCustomer();
  }

  Future<void> _prefillCustomer() async {
    final session = SessionService();
    final values = await Future.wait([
      session.getUserName(),
      session.getUserPhone(),
      session.getUserEmail(),
    ]);
    if (!mounted) return;
    setState(() {
      _name.text = values[0] == 'Invitado' ? '' : values[0];
      _phone.text = values[1];
      _email.text = values[2];
    });
  }

  Future<void> _useCurrentLocation() async {
    try {
      final serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        throw Exception('Activa la ubicacion del dispositivo para usar esta opcion.');
      }

      var permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }
      if (permission == LocationPermission.denied ||
          permission == LocationPermission.deniedForever) {
        throw Exception('No diste permiso para acceder a tu ubicacion.');
      }

      final position = await Geolocator.getCurrentPosition();
      final lookup = await _locationLookupService.reverseGeocode(
        latitude: position.latitude,
        longitude: position.longitude,
      );
      if (!mounted) return;
      setState(() {
        _address.text = lookup.address;
        _reference.text = lookup.reference;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Ubicacion actual cargada correctamente.')),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString().replaceFirst('Exception: ', ''))),
      );
    }
  }

  @override
  void dispose() {
    for (final controller in [
      _name,
      _phone,
      _email,
      _address,
      _reference,
      _notes,
    ]) {
      controller.dispose();
    }
    super.dispose();
  }

  bool _drinkProduct(Producto p) => RegExp(
    r'bebida|gaseosa|agua|chicha|limonada|coca|inca|sprite',
    caseSensitive: false,
  ).hasMatch('${p.categoria} ${p.name}');
  bool _sideProduct(Producto p) => RegExp(
    r'acompa|guarnici|papas|arroz|camote|yuca',
    caseSensitive: false,
  ).hasMatch('${p.categoria} ${p.name}');
  InputDecoration _decor(String label) => InputDecoration(
    labelText: label,
    filled: true,
    fillColor: Colors.white,
    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
  );
  @override
  Widget build(BuildContext context) => FractionallySizedBox(
    heightFactor: .9,
    child: FutureBuilder<List<Producto>>(
      future: _products,
      builder: (context, snap) {
        if (snap.connectionState != ConnectionState.done)
          return const Center(child: CircularProgressIndicator());
        if (snap.hasError)
          return const Center(child: Text('No se pudo cargar el catálogo.'));
        final all = snap.data ?? const <Producto>[];
        final drinks = all.where(_drinkProduct).toList();
        final sides = all.where(_sideProduct).toList();
        final dishes = all
            .where(
              (p) =>
                  !_drinkProduct(p) &&
                  !_sideProduct(p) &&
                  !p.categoria.toLowerCase().contains('ensalada'),
            )
            .toList();
        if (all.isEmpty)
          return const Center(child: Text('No hay productos disponibles.'));
        final pages = <Widget>[
          Column(
            children: [
              DropdownButtonFormField<Producto>(
                value: _dish,
                decoration: _decor('¿Qué plato deseas pedir?'),
                items: dishes
                    .map(
                      (p) => DropdownMenuItem(
                        value: p,
                        child: Text(
                          '${p.name} · S/ ${p.price.toStringAsFixed(2)}',
                        ),
                      ),
                    )
                    .toList(),
                onChanged: (v) => setState(() => _dish = v),
                validator: (v) => v == null ? 'Selecciona un plato' : null,
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  IconButton(
                    onPressed: _qty > 1 ? () => setState(() => _qty--) : null,
                    icon: const Icon(Icons.remove),
                  ),
                  Text(
                    '$_qty',
                    style: const TextStyle(fontWeight: FontWeight.bold),
                  ),
                  IconButton(
                    onPressed: () => setState(() => _qty++),
                    icon: const Icon(Icons.add),
                  ),
                ],
              ),
            ],
          ),
          Column(
            children: [
              if (sides.isNotEmpty)
                DropdownButtonFormField<Producto>(
                  value: _side,
                  isExpanded: true,
                  decoration: _decor('Acompañamiento'),
                  items: [
                    const DropdownMenuItem(
                      value: null,
                      child: Text('Sin acompañamiento'),
                    ),
                    ...sides.map(
                      (p) => DropdownMenuItem(value: p, child: Text(p.name)),
                    ),
                  ],
                  onChanged: (v) => setState(() => _side = v),
                ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                value: _salad,
                isExpanded: true,
                decoration: _decor('Ensalada'),
                items: const [
                  DropdownMenuItem(value: '', child: Text('Sin ensalada')),
                  DropdownMenuItem(value: 'dulce', child: Text('Dulce')),
                  DropdownMenuItem(value: 'salada', child: Text('Salada')),
                ],
                onChanged: (v) => setState(() => _salad = v ?? ''),
              ),
              const SizedBox(height: 12),
              if (drinks.isNotEmpty)
                DropdownButtonFormField<Producto>(
                  value: _drink,
                  isExpanded: true,
                  decoration: _decor('Bebida'),
                  items: [
                    const DropdownMenuItem(
                      value: null,
                      child: Text('Sin bebida'),
                    ),
                    ...drinks.map(
                      (p) => DropdownMenuItem(
                        value: p,
                        child: Text(
                          '${p.name} · S/ ${p.price.toStringAsFixed(2)}',
                        ),
                      ),
                    ),
                  ],
                  onChanged: (v) => setState(() => _drink = v),
                ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _notes,
                maxLength: 255,
                maxLines: 2,
                decoration: _decor('Indicaciones para preparar tu pedido'),
              ),
            ],
          ),
          Column(
            children: [
              DropdownButtonFormField<String>(
                value: _delivery,
                isExpanded: true,
                decoration: _decor('Modalidad'),
                items: const [
                  DropdownMenuItem(value: 'delivery', child: Text('Delivery')),
                  DropdownMenuItem(
                    value: 'pickup',
                    child: Text('Recojo en local'),
                  ),
                ],
                onChanged: (v) => setState(() => _delivery = v ?? 'delivery'),
              ),
              if (_delivery == 'delivery') ...[
                const SizedBox(height: 12),
                TextFormField(
                  controller: _address,
                  decoration: _decor('Dirección'),
                  validator: (v) =>
                      _delivery == 'delivery' && (v ?? '').trim().isEmpty
                      ? 'Dirección obligatoria'
                      : null,
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _reference,
                  decoration: _decor('Referencia'),
                ),
                const SizedBox(height: 12),
                OutlinedButton.icon(
                  onPressed: _useCurrentLocation,
                  icon: const Icon(Icons.my_location_outlined),
                  label: const Text('Usar mi ubicación'),
                ),
              ],
            ],
          ),
          Column(
            children: [
              TextFormField(
                controller: _name,
                autofillHints: const [AutofillHints.name],
                decoration: _decor('Nombre completo'),
                validator: (v) =>
                    (v ?? '').trim().isEmpty ? 'Nombre obligatorio' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _phone,
                autofillHints: const [AutofillHints.telephoneNumber],
                keyboardType: TextInputType.phone,
                inputFormatters: [
                  FilteringTextInputFormatter.digitsOnly,
                  LengthLimitingTextInputFormatter(9),
                ],
                decoration: _decor('Teléfono'),
                validator: (v) => RegExp(r'^\d{9}$').hasMatch((v ?? '').trim())
                    ? null
                    : 'Ingresa los 9 digitos de tu celular',
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _email,
                autofillHints: const [AutofillHints.email],
                keyboardType: TextInputType.emailAddress,
                decoration: _decor('Correo electrónico').copyWith(
                  helperText: _email.text.trim().isEmpty
                      ? 'Se completará con el correo de tu cuenta.'
                      : 'Correo recuperado de tu cuenta.',
                ),
                validator: (v) =>
                    RegExp(r'^\S+@\S+\.\S+$').hasMatch((v ?? '').trim())
                    ? null
                    : 'Correo inválido',
              ),
            ],
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                '${_dish?.name ?? 'Plato'} × $_qty',
                style: const TextStyle(fontWeight: FontWeight.bold),
              ),
              if (_side != null) Text(_side!.name),
              if (_drink != null) Text('${_drink!.name} × $_drinkQty'),
              Text(_delivery == 'delivery' ? 'Delivery' : 'Recojo en local'),
              const SizedBox(height: 12),
              Text(
                'Subtotal de productos: S/ ${((_dish?.price ?? 0) * _qty + (_side?.price ?? 0) + (_drink?.price ?? 0) * _drinkQty).toStringAsFixed(2)}',
              ),
            ],
          ),
        ];
        return SafeArea(
          child: Padding(
            padding: EdgeInsets.fromLTRB(
              16,
              16,
              16,
              MediaQuery.viewInsetsOf(context).bottom + 16,
            ),
            child: Form(
              key: _formKey,
              child: Column(
                children: [
                  const Text(
                    'Compra guiada · El Dorado',
                    style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900),
                  ),
                  const SizedBox(height: 12),
                  Expanded(child: SingleChildScrollView(child: pages[_step])),
                  Row(
                    children: [
                      if (_step > 0)
                        TextButton(
                          onPressed: () => setState(() => _step--),
                          child: const Text('Anterior'),
                        ),
                      const Spacer(),
                      TextButton(
                        onPressed: () => Navigator.pop(context),
                        child: const Text('Cancelar'),
                      ),
                      FilledButton(
                        onPressed: () {
                          if (!_formKey.currentState!.validate()) return;
                          if (_step < 4) {
                            setState(() => _step++);
                            return;
                          }
                          final cart = CartScope.of(context);
                          for (var i = 0; i < _qty; i++) cart.add(_dish!);
                          if (_side != null) cart.add(_side!);
                          if (_drink != null)
                            for (var i = 0; i < _drinkQty; i++)
                              cart.add(_drink!);
                          cart.setDeliveryType(_delivery == 'delivery');
                          if (_delivery == 'delivery')
                            cart.setAddress(
                              addressValue: _address.text.trim(),
                              referenceValue: _reference.text.trim(),
                            );
                          cart.setGuidedCheckout(
                            name: _name.text.trim(),
                            phone: _phone.text.trim(),
                            email: _email.text.trim(),
                            note: _notes.text.trim(),
                            salad: _salad,
                          );
                          Navigator.pop(context);
                          context.go('/app');
                        },
                        child: Text(
                          _step < 4 ? 'Continuar' : 'Agregar al carrito',
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        );
      },
    ),
  );
}

class _ChatSection {
  const _ChatSection({this.title, required this.lines});

  final String? title;
  final List<String> lines;
}

const List<String> _chatBulletMarkers = ['- ', '• ', '* ', '· '];

String? _stripBulletMarker(String line) {
  final trimmed = line.trim();
  for (final marker in _chatBulletMarkers) {
    if (trimmed.startsWith(marker)) return trimmed.substring(marker.length).trim();
  }
  return null;
}

/// El modelo no siempre respeta al pie de la letra el formato "## "/"- " que
/// le pedimos en el prompt, pero cuando arma una lista (productos, estado de
/// pedido) casi siempre agrupa con una linea de titulo seguida de lineas con
/// alguna vinieta ("-", "•", "*"). Detectamos ese patron en vez de depender
/// de un marcador exacto, para que las cajas se vean sin importar el estilo
/// exacto de la respuesta.
/// Devuelve una lista vacia cuando el mensaje no parece una lista (charla
/// normal): en ese caso el llamador debe mostrar el texto plano de siempre.
List<_ChatSection> _parseChatSections(String text) {
  final rawLines = text.split('\n').map((l) => l.trim()).toList();
  final bulletCount = rawLines.where((l) => _stripBulletMarker(l) != null).length;
  if (bulletCount < 2) {
    return const [];
  }

  final sections = <_ChatSection>[];
  String? currentTitle;
  var currentLines = <String>[];

  void flush() {
    if (currentTitle != null || currentLines.isNotEmpty) {
      sections.add(_ChatSection(title: currentTitle, lines: currentLines));
    }
  }

  for (final line in rawLines) {
    if (line.isEmpty) continue;
    final bulletText = _stripBulletMarker(line);
    if (bulletText != null) {
      currentLines.add(bulletText);
      continue;
    }
    // Linea sin vinieta: es el titulo de una nueva seccion (p.ej. "## " o
    // simplemente el nombre de la categoria en su propia linea).
    flush();
    currentTitle = line.startsWith('## ') ? line.substring(3).trim() : line;
    currentLines = [];
  }
  flush();
  return sections;
}

enum _ChatRole { user, bot }

class _ChatMessage {
  final _ChatRole role;
  final String text;
  final List<String> suggestions;
  final bool isTyping;

  const _ChatMessage({
    required this.role,
    required this.text,
    this.suggestions = const [],
    this.isTyping = false,
  });
}
