import '../models/promotion_offer.dart';
import 'api_client.dart';

class PromotionService {
  Future<PromotionOffer?> fetchActive() async {
    final offers = await fetchActiveList();
    return offers.isNotEmpty ? offers.first : null;
  }

  // Hasta 3 promociones vigentes a la vez, para el carrusel de la caja
  // destacada del inicio (web y movil comparten el mismo limite).
  Future<List<PromotionOffer>> fetchActiveList() async {
    try {
      final res = await ApiClient.get<Map<String, dynamic>>('/promotions/active');
      final data = (res.data ?? <String, dynamic>{}).cast<String, dynamic>();
      if (data['active'] != true) return const [];

      final rawOffers = data['offers'];
      if (rawOffers is List && rawOffers.isNotEmpty) {
        return rawOffers
            .map((item) => PromotionOffer.fromJson((item as Map).cast<String, dynamic>()))
            .toList();
      }

      if (data['offer'] != null) {
        return [PromotionOffer.fromJson((data['offer'] as Map).cast<String, dynamic>())];
      }

      return const [];
    } catch (_) {
      return const [];
    }
  }
}
