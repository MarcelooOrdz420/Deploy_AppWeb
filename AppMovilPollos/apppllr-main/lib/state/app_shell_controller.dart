import 'package:flutter/foundation.dart';

class AppShellController {
  AppShellController._();

  static final AppShellController instance = AppShellController._();

  final ValueNotifier<int> tabIndex = ValueNotifier<int>(0);

  // Se incrementa cada vez que llega una promocion por tiempo real, para que
  // la caja destacada del inicio se refresque sola sin interrumpir al
  // cliente con un dialogo emergente mientras ya esta usando la app.
  final ValueNotifier<int> promotionRefreshTick = ValueNotifier<int>(0);

  void goTo(int index) {
    tabIndex.value = index;
  }

  void refreshPromotions() {
    promotionRefreshTick.value++;
  }
}
