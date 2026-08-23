import '../models/promotion_offer.dart';
import 'api_client.dart';

class PromotionService {
  Future<PromotionOffer?> fetchActive() async {
    try {
      final res = await ApiClient.get<Map<String, dynamic>>('/promotions/active');
      final data = (res.data ?? <String, dynamic>{}).cast<String, dynamic>();
      if (data['active'] != true || data['offer'] == null) return null;
      return PromotionOffer.fromJson((data['offer'] as Map).cast<String, dynamic>());
    } catch (_) {
      return null;
    }
  }
}
