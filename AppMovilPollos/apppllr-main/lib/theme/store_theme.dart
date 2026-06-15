import 'package:flutter/material.dart';

class StoreTheme {
  static const Color orange = Color(0xFFFF9F22);
  static const Color orangeSoft = Color(0xFFFFC20E);
  static const Color orangeDeep = Color(0xFFD87525);
  static const Color cream = Color(0xFFFFF8ED);
  static const Color paper = Color(0xFFFFFCF6);
  static const Color paperSoft = Color(0xFFFFF1DC);
  static const Color ink = Color(0xFF1B130F);
  static const Color inkSoft = Color(0xFF594133);
  static const Color lineStrong = Color(0xFFFFC061);

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
        backgroundColor: Color(0xFF17110D),
        selectedItemColor: orangeDeep,
        unselectedItemColor: Color(0xFFFFE8BF),
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
      scaffoldBackgroundColor: const Color(0xFF120D09),
      fontFamily: 'Trebuchet MS',
    );

    return base.copyWith(
      textTheme: base.textTheme.apply(
        bodyColor: const Color(0xFFF8EBDD),
        displayColor: const Color(0xFFF8EBDD),
      ),
      snackBarTheme: const SnackBarThemeData(
        behavior: SnackBarBehavior.floating,
        backgroundColor: Color(0xFFFFF1E3),
        contentTextStyle: TextStyle(color: Color(0xFF25170F)),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: const Color(0xFF1B140F),
        hintStyle: const TextStyle(color: Color(0xFFD2B59B)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(18),
          borderSide: const BorderSide(color: Color(0xFF8C6449)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(18),
          borderSide: const BorderSide(color: Color(0xFF8C6449)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(18),
          borderSide: const BorderSide(color: Color(0xFFFFB36F), width: 1.4),
        ),
      ),
      bottomNavigationBarTheme: const BottomNavigationBarThemeData(
        backgroundColor: Color(0xFF0F0B08),
        selectedItemColor: Color(0xFFFFB36F),
        unselectedItemColor: Color(0xFFD2B59B),
        showUnselectedLabels: true,
        type: BottomNavigationBarType.fixed,
      ),
    );
  }

  static const LinearGradient appGradient = LinearGradient(
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
    colors: <Color>[
      Color(0xFFFFFBF6),
      Color(0xFFFFF4E1),
      Color(0xFFFFE8BA),
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
          Colors.white.withOpacity(.84),
          cream.withOpacity(.94),
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
