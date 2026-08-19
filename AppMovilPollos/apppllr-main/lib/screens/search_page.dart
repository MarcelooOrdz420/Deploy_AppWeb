import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../models/producto.dart';
import '../services/productos_service.dart';
import '../state/cart_controller.dart';
import '../theme/store_theme.dart';
import '../widgets/producto_image.dart';

class SearchPage extends StatefulWidget {
  const SearchPage({super.key});

  @override
  State<SearchPage> createState() => _SearchPageState();
}

class _SearchPageState extends State<SearchPage> {
  final _ctrl = TextEditingController();
  late Future<List<Producto>> _future;
  List<Producto> _all = [];
  List<Producto> _filtered = [];

  @override
  void initState() {
    super.initState();
    _future = ProductosService().listar();
  }

  void _filter(String q) {
    final query = q.trim().toLowerCase();
    setState(() {
      _filtered = query.isEmpty
          ? _all
          : _all.where((p) => p.name.toLowerCase().contains(query)).toList();
    });
  }

  @override
  Widget build(BuildContext context) {
    final cart = CartScope.of(context);

    return Scaffold(
      backgroundColor: StoreTheme.background,
      appBar: AppBar(
        toolbarHeight: 86,
        title: const Text(
          'Buscar',
          style: TextStyle(fontSize: 32, fontWeight: FontWeight.w900),
        ),
        automaticallyImplyLeading: false,
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 4, 16, 14),
            child: TextField(
              controller: _ctrl,
              onChanged: _filter,
              decoration: InputDecoration(
                hintText: 'Buscar por pollo, combos, bebidas...',
                prefixIcon: const Icon(Icons.search_rounded, size: 28),
                suffixIcon: _ctrl.text.isEmpty
                    ? null
                    : IconButton(
                        icon: const Icon(Icons.close_rounded),
                        onPressed: () {
                          _ctrl.clear();
                          _filter('');
                        },
                      ),
              ),
            ),
          ),
          Expanded(
            child: FutureBuilder<List<Producto>>(
              future: _future,
              builder: (context, snap) {
                if (snap.connectionState != ConnectionState.done) {
                  return const Center(child: CircularProgressIndicator());
                }
                if (snap.hasError)
                  return Center(child: Text('Error: ${snap.error}'));

                _all = snap.data!;
                if (_filtered.isEmpty && _ctrl.text.trim().isEmpty)
                  _filtered = _all;

                return ListView.separated(
                  padding: const EdgeInsets.fromLTRB(16, 2, 16, 20),
                  itemCount: _filtered.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 10),
                  itemBuilder: (context, i) {
                    final p = _filtered[i];
                    return ListTile(
                      contentPadding: const EdgeInsets.all(12),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(24),
                        side: const BorderSide(color: StoreTheme.borderSoft),
                      ),
                      tileColor: Colors.white,
                      leading: ProductoImage(
                        producto: p,
                        width: 72,
                        height: 72,
                        borderRadius: BorderRadius.circular(18),
                      ),
                      title: Text(
                        p.name,
                        style: const TextStyle(
                          fontSize: 17,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                      subtitle: Text(
                        'S/ ${p.price.toStringAsFixed(2)}',
                        style: const TextStyle(
                          color: StoreTheme.orangeDeep,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      trailing: IconButton(
                        icon: const Icon(
                          Icons.add_circle,
                          color: StoreTheme.orange,
                          size: 34,
                        ),
                        onPressed: () {
                          cart.add(p);
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text('${p.name} agregado al carrito'),
                            ),
                          );
                        },
                      ),
                      onTap: () => context.push('/detalles/${p.id}'),
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
