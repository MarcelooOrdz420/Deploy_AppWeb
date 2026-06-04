import 'dart:convert';

import 'package:http/http.dart' as http;

class LocationLookupResult {
  const LocationLookupResult({
    required this.address,
    required this.reference,
  });

  final String address;
  final String reference;
}

class LocationLookupService {
  Future<LocationLookupResult> reverseGeocode({
    required double latitude,
    required double longitude,
  }) async {
    final uri = Uri.https(
      'nominatim.openstreetmap.org',
      '/reverse',
      <String, String>{
        'format': 'jsonv2',
        'lat': latitude.toStringAsFixed(7),
        'lon': longitude.toStringAsFixed(7),
        'zoom': '18',
        'addressdetails': '1',
      },
    );

    final response = await http
        .get(
          uri,
          headers: const <String, String>{
            'Accept': 'application/json',
            'User-Agent': 'web-polleria-mobile/1.0',
          },
        )
        .timeout(const Duration(seconds: 8));

    if (response.statusCode < 200 || response.statusCode >= 300) {
      throw Exception('No se pudo traducir la ubicacion a calles cercanas.');
    }

    final decoded = jsonDecode(response.body);
    if (decoded is! Map<String, dynamic>) {
      throw Exception('La respuesta de ubicacion no tiene un formato valido.');
    }

    final address = (decoded['address'] as Map? ?? const <String, dynamic>{})
        .cast<String, dynamic>();

    final road = _value(address, const ['road', 'pedestrian', 'residential', 'cycleway']);
    final avenue = _value(address, const ['avenue']);
    final houseNumber = _value(address, const ['house_number']);
    final suburb = _value(address, const ['suburb', 'neighbourhood', 'city_district']);
    final city = _value(address, const ['city', 'town', 'village', 'county']);
    final state = _value(address, const ['state']);
    final amenity = _value(address, const ['amenity', 'shop', 'tourism']);

    final exactPlace = [
      road.isNotEmpty ? road : avenue,
      houseNumber,
    ].where((item) => item.isNotEmpty).join(' ').trim();

    final resolvedAddress = exactPlace.isNotEmpty
        ? exactPlace
        : _value(decoded, const ['name', 'display_name'], fallback: 'Ubicacion detectada');

    final reference = <String>[
      if (amenity.isNotEmpty) 'Cerca de $amenity',
      if (suburb.isNotEmpty) 'Zona $suburb',
      if (city.isNotEmpty) 'Distrito/Ciudad $city',
      if (state.isNotEmpty && state != city) state,
    ].join(' | ');

    return LocationLookupResult(
      address: resolvedAddress,
      reference: reference.isEmpty ? 'Ubicacion obtenida desde GPS' : reference,
    );
  }

  String _value(
    Map<String, dynamic> source,
    List<String> keys, {
    String fallback = '',
  }) {
    for (final key in keys) {
      final value = (source[key] ?? '').toString().trim();
      if (value.isNotEmpty) return value;
    }
    return fallback;
  }
}
