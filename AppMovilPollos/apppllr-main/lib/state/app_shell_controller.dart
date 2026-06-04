import 'package:flutter/foundation.dart';

class AppShellController {
  AppShellController._();

  static final AppShellController instance = AppShellController._();

  final ValueNotifier<int> tabIndex = ValueNotifier<int>(0);

  void goTo(int index) {
    tabIndex.value = index;
  }
}
