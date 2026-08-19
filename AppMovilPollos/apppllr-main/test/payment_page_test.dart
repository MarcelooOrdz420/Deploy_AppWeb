import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'package:flutter_application_1/screens/payment_page.dart';
import 'package:flutter_application_1/state/cart_controller.dart';
import 'package:flutter_application_1/theme/store_theme.dart';

void main() {
  testWidgets('checkout móvil muestra tarjeta y contraentrega sin Yape', (
    tester,
  ) async {
    SharedPreferences.setMockInitialValues(<String, Object>{});
    tester.view.physicalSize = const Size(720, 3200);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    await tester.pumpWidget(
      CartScope(
        controller: CartController(),
        child: MaterialApp(
          theme: StoreTheme.theme(),
          home: const PaymentPage(),
        ),
      ),
    );
    await tester.pump();

    expect(find.text('Tarjeta / Izipay'), findsOneWidget);
    expect(find.text('Yape'), findsNothing);
    expect(find.text('Contraentrega'), findsOneWidget);
    expect(find.text('¡Tu pollo ya casi está listo!'), findsOneWidget);
  });
}
