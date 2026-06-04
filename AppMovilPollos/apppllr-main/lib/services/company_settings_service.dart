import '../models/company_settings.dart';
import 'api_client.dart';

class CompanySettingsService {
  Future<CompanySettings> fetch() async {
    try {
      final res = await ApiClient.get<Map<String, dynamic>>('/settings/public');
      final data = (res.data ?? <String, dynamic>{}).cast<String, dynamic>();
      return CompanySettings.fromJson(data);
    } catch (_) {
      return CompanySettings.fallback();
    }
  }
}
