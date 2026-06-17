import 'package:flutter/material.dart';

class StoreTheme {
  static const Color orange = Color(0xFFF97316);
  static const Color orangeSoft = Color(0xFFF8B11B);
  static const Color orangeDeep = Color(0xFFC2410C);
  static const Color cream = Color(0xFFFFF4DF);
  static const Color paper = Color(0xFFFFFFFF);
  static const Color paperSoft = Color(0xFFFFFAF2);
  static const Color ink = Color(0xFF0F0F10);
  static const Color inkSoft = Color(0xFF6F5745);
  static const Color lineStrong = Color(0xFFF8B11B);

  static ThemeData theme() {
    final base = ThemeData(
      useMaterial3: true,
      colorScheme: ColorScheme.fromSeed(
        seedColor: orange,
        primary: orange,
        secondary: orangeSoft,
        brightness: Brightness.light,
      ),
      scaffoldBackgroundColor: Colors.transparent,
      fontFamily: 'Trebuchet MS',
    );

    return base.copyWith(
      textTheme: base.textTheme.apply(bodyColor: ink, displayColor: ink),
      snackBarTheme: const SnackBarThemeData(
        behavior: SnackBarBehavior.floating,
        backgroundColor: ink,
        contentTextStyle: TextStyle(color: cream),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: paper,
        hintStyle: const TextStyle(color: inkSoft),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(18),
          borderSide: const BorderSide(color: lineStrong),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(18),
          borderSide: const BorderSide(color: lineStrong),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(18),
          borderSide: const BorderSide(color: orangeSoft, width: 1.4),
        ),
      ),
      bottomNavigationBarTheme: const BottomNavigationBarThemeData(
        backgroundColor: ink,
        selectedItemColor: orangeSoft,
        unselectedItemColor: Color(0xFFFFE2A3),
        showUnselectedLabels: true,
        type: BottomNavigationBarType.fixed,
      ),
    );
  }

  static ThemeData darkTheme() {
    final base = ThemeData(
      useMaterial3: true,
      colorScheme: ColorScheme.fromSeed(
        seedColor: orange,
        primary: orangeSoft,
        secondary: const Color(0xFFFFC078),
        brightness: Brightness.dark,
      ),
      scaffoldBackgroundColor: const Color(0xFF090909),
      fontFamily: 'Trebuchet MS',
    );

    return base.copyWith(
      textTheme: base.textTheme.apply(
        bodyColor: const Color(0xFFF8EBDD),
        displayColor: const Color(0xFFF8EBDD),
      ),
      snackBarTheme: const SnackBarThemeData(
        behavior: SnackBarBehavior.floating,
        backgroundColor: Color(0xFFFFF4DF),
        contentTextStyle: TextStyle(color: Color(0xFF0F0F10)),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: const Color(0xFF17110D),
        hintStyle: const TextStyle(color: Color(0xFFFFE2A3)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(18),
          borderSide: const BorderSide(color: Color(0xFFF8B11B)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(18),
          borderSide: const BorderSide(color: Color(0xFFF8B11B)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(18),
          borderSide: const BorderSide(color: orange, width: 1.4),
        ),
      ),
      bottomNavigationBarTheme: const BottomNavigationBarThemeData(
        backgroundColor: Color(0xFF090909),
        selectedItemColor: orangeSoft,
        unselectedItemColor: Color(0xFFFFE2A3),
        showUnselectedLabels: true,
        type: BottomNavigationBarType.fixed,
      ),
    );
  }

  static const LinearGradient appGradient = LinearGradient(
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
    colors: <Color>[
      Color(0xFF080604),
      Color(0xFF17110D),
      Color(0xFF2B1306),
    ],
  );

  static BoxDecoration frameDecoration() {
    return BoxDecoration(
      borderRadius: BorderRadius.circular(12),
      border: Border.all(color: lineStrong.withOpacity(.72)),
      gradient: LinearGradient(
        begin: Alignment.topCenter,
        end: Alignment.bottomCenter,
        colors: <Color>[
          paper,
          cream,
        ],
      ),
      boxShadow: const <BoxShadow>[
        BoxShadow(
          color: Color.fromRGBO(52, 17, 0, .13),
          blurRadius: 40,
          offset: Offset(0, 18),
        ),
      ],
    );
  }

  static BoxDecoration surfaceDecoration() {
    return BoxDecoration(
      borderRadius: BorderRadius.circular(8),
      border: Border.all(color: lineStrong.withOpacity(.74)),
      gradient: const LinearGradient(
        begin: Alignment.topCenter,
        end: Alignment.bottomCenter,
        colors: <Color>[paper, paperSoft],
      ),
      boxShadow: const <BoxShadow>[
        BoxShadow(
          color: Color.fromRGBO(52, 17, 0, .07),
          blurRadius: 28,
          offset: Offset(0, 14),
        ),
      ],
    );
  }

  static BoxDecoration panelDecoration() {
    return BoxDecoration(
      borderRadius: BorderRadius.circular(8),
      border: Border.all(color: lineStrong.withOpacity(.7)),
      gradient: const LinearGradient(
        begin: Alignment.topCenter,
        end: Alignment.bottomCenter,
        colors: <Color>[paper, paperSoft],
      ),
    );
  }
}

class StoreBackdrop extends StatelessWidget {
  const StoreBackdrop({super.key, required this.child});

  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(gradient: StoreTheme.appGradient),
      child: child,
    );
  }
}

class StoreFrame extends StatelessWidget {
  const StoreFrame({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(12),
  });

  final Widget child;
  final EdgeInsetsGeometry padding;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: padding,
      child: DecoratedBox(
        decoration: StoreTheme.frameDecoration(),
        child: child,
      ),
    );
  }
}

class StoreSurface extends StatelessWidget {
  const StoreSurface({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(18),
    this.margin = EdgeInsets.zero,
  });

  final Widget child;
  final EdgeInsetsGeometry padding;
  final EdgeInsetsGeometry margin;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: margin,
      padding: padding,
      decoration: StoreTheme.surfaceDecoration(),
      child: child,
    );
  }
}

class StorePanel extends StatelessWidget {
  const StorePanel({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(16),
  });

  final Widget child;
  final EdgeInsetsGeometry padding;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: padding,
      decoration: StoreTheme.panelDecoration(),
      child: child,
    );
  }
}
