import 'package:flutter/material.dart';

class StoreTheme {
  static const Color orange = Color(0xFFF2B705);
  static const Color orangeSoft = Color(0xFFFFCF3A);
  static const Color orangeDeep = Color(0xFFD97904);
  static const Color cream = Color(0xFFFFF8EC);
  static const Color paper = Color(0xFFFFFFFF);
  static const Color paperSoft = Color(0xFFFFF4DF);
  static const Color ink = Color(0xFF1F140D);
  static const Color inkSoft = Color(0xFF6D5541);
  static const Color lineStrong = Color(0xFFF2B705);

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
      fontFamily: 'Segoe UI',
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
          borderSide: const BorderSide(color: orange, width: 1.4),
        ),
      ),
      bottomNavigationBarTheme: const BottomNavigationBarThemeData(
        backgroundColor: Color(0xFF100D0A),
        selectedItemColor: orangeSoft,
        unselectedItemColor: Color(0xFFD8C8B5),
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
        secondary: orangeDeep,
        brightness: Brightness.dark,
      ),
      scaffoldBackgroundColor: const Color(0xFF100D0A),
      fontFamily: 'Segoe UI',
    );

    return base.copyWith(
      textTheme: base.textTheme.apply(
        bodyColor: cream,
        displayColor: cream,
      ),
      snackBarTheme: const SnackBarThemeData(
        behavior: SnackBarBehavior.floating,
        backgroundColor: orangeSoft,
        contentTextStyle: TextStyle(color: ink),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: const Color(0xFF1B130D),
        hintStyle: const TextStyle(color: Color(0xFFD8C8B5)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(18),
          borderSide: const BorderSide(color: Color(0x66F2B705)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(18),
          borderSide: const BorderSide(color: Color(0x66F2B705)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(18),
          borderSide: const BorderSide(color: orangeSoft, width: 1.4),
        ),
      ),
      bottomNavigationBarTheme: const BottomNavigationBarThemeData(
        backgroundColor: Color(0xFF100D0A),
        selectedItemColor: orangeSoft,
        unselectedItemColor: Color(0xFFD8C8B5),
        showUnselectedLabels: true,
        type: BottomNavigationBarType.fixed,
      ),
    );
  }

  static const LinearGradient appGradient = LinearGradient(
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
    colors: <Color>[
      Color(0xFF1A1009),
      Color(0xFF100D0A),
      Color(0xFF24150C),
    ],
  );

  static BoxDecoration frameDecoration() {
    return BoxDecoration(
      borderRadius: BorderRadius.circular(18),
      border: Border.all(color: Colors.white.withOpacity(.10)),
      gradient: LinearGradient(
        begin: Alignment.topCenter,
        end: Alignment.bottomCenter,
        colors: <Color>[
          const Color(0xFF17100B),
          const Color(0xFF100D0A),
        ],
      ),
      boxShadow: const <BoxShadow>[
        BoxShadow(
          color: Color.fromRGBO(8, 5, 2, .36),
          blurRadius: 44,
          offset: Offset(0, 22),
        ),
      ],
    );
  }

  static BoxDecoration surfaceDecoration() {
    return BoxDecoration(
      borderRadius: BorderRadius.circular(12),
      border: Border.all(color: lineStrong.withOpacity(.22)),
      gradient: const LinearGradient(
        begin: Alignment.topCenter,
        end: Alignment.bottomCenter,
        colors: <Color>[Color(0xFFFFFFFF), Color(0xFFFFF8EC)],
      ),
      boxShadow: const <BoxShadow>[
        BoxShadow(
          color: Color.fromRGBO(31, 20, 13, .13),
          blurRadius: 28,
          offset: Offset(0, 14),
        ),
      ],
    );
  }

  static BoxDecoration panelDecoration() {
    return BoxDecoration(
      borderRadius: BorderRadius.circular(12),
      border: Border.all(color: lineStrong.withOpacity(.22)),
      gradient: const LinearGradient(
        begin: Alignment.topCenter,
        end: Alignment.bottomCenter,
        colors: <Color>[Color(0xFFFFFFFF), Color(0xFFFFF8EC)],
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
