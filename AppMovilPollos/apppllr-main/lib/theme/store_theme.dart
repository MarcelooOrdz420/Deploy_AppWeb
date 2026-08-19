import 'package:flutter/material.dart';

class StoreTheme {
  static const Color orange = Color(0xFFFF6F1F);
  static const Color orangeHover = Color(0xFFE95C0D);
  static const Color orangeDark = Color(0xFFC94700);
  static const Color orangeSoft = Color(0xFFFF9D5A);
  static const Color gold = Color(0xFFF7B801);
  static const Color goldDark = Color(0xFFDFA500);
  static const Color goldSoft = Color(0xFFFFF7D6);
  static const Color cream = Color(0xFFFFF8F2);
  static const Color creamStrong = Color(0xFFFFF1E3);
  static const Color surface = Color(0xFFFFFFFF);
  static const Color surfaceWarm = Color(0xFFFFFDF9);
  static const Color surfaceSoft = Color(0xFFFFF6EE);
  static const Color field = Color(0xFFFFF4EB);
  static const Color textPrimary = Color(0xFF25170F);
  static const Color textDark = Color(0xFF24160F);
  static const Color textSecondary = Color(0xFF68432E);
  static const Color textMuted = Color(0xFF765744);
  static const Color border = Color(0xFFEAB68A);
  static const Color borderSoft = Color(0xFFF0C9AA);
  static const Color borderLight = Color(0xFFF0CFB3);
  static const Color success = Color(0xFF17683A);
  static const Color danger = Color(0xFFA1261A);
  static const Color warning = Color(0xFF805100);
  static const Color info = Color(0xFF205A84);

  static const Color primary = orange;
  static const Color primaryDark = orangeDark;
  static const Color primarySoft = creamStrong;
  static const Color accent = gold;
  static const Color accentDark = goldDark;
  static const Color accentSoft = goldSoft;
  static const Color background = cream;
  static const Color backgroundAlt = creamStrong;
  static const Color paper = surfaceWarm;
  static const Color paperSoft = surfaceSoft;
  static const Color ink = textPrimary;
  static const Color inkSoft = textSecondary;
  static const Color lineStrong = border;
  static const Color orangeDeep = orangeDark;

  static ThemeData theme() {
    final base = ThemeData(
      useMaterial3: true,
      colorScheme: ColorScheme.fromSeed(
        seedColor: orange,
        primary: orange,
        secondary: orangeSoft,
        brightness: Brightness.light,
      ),
      scaffoldBackgroundColor: background,
    );

    return base.copyWith(
      textTheme: base.textTheme.copyWith(
        displayLarge: const TextStyle(color: textPrimary, fontSize: 48, fontWeight: FontWeight.w800),
        headlineLarge: const TextStyle(color: textPrimary, fontSize: 34, fontWeight: FontWeight.w800),
        headlineMedium: const TextStyle(color: textPrimary, fontSize: 28, fontWeight: FontWeight.w800),
        titleLarge: const TextStyle(color: textPrimary, fontSize: 22, fontWeight: FontWeight.w700),
        titleMedium: const TextStyle(color: textPrimary, fontSize: 18, fontWeight: FontWeight.w700),
        bodyLarge: const TextStyle(color: textPrimary, fontSize: 16, height: 1.45),
        bodyMedium: const TextStyle(color: textPrimary, fontSize: 14, height: 1.45),
        bodySmall: const TextStyle(color: textSecondary, fontSize: 12, height: 1.4),
        labelLarge: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700),
      ),
      appBarTheme: const AppBarTheme(
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: false,
        backgroundColor: cream,
        foregroundColor: textPrimary,
        titleTextStyle: TextStyle(
          color: textPrimary,
          fontSize: 20,
          fontWeight: FontWeight.w900,
          letterSpacing: -.3,
        ),
      ),
      snackBarTheme: const SnackBarThemeData(
        behavior: SnackBarBehavior.floating,
        backgroundColor: ink,
        contentTextStyle: TextStyle(color: cream),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: surface,
        hintStyle: const TextStyle(color: textMuted),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: lineStrong),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: lineStrong),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: orange, width: 1.6),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: danger),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: danger, width: 2),
        ),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          minimumSize: const Size(48, 48),
          backgroundColor: primary,
          foregroundColor: Colors.white,
          disabledBackgroundColor: borderSoft,
          disabledForegroundColor: textMuted,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          minimumSize: const Size(48, 48),
          backgroundColor: orange,
          foregroundColor: Colors.white,
          elevation: 1,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          minimumSize: const Size(48, 48),
          foregroundColor: primaryDark,
          side: const BorderSide(color: border),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        ),
      ),
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          minimumSize: const Size(48, 48),
          foregroundColor: orangeDark,
          textStyle: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700),
        ),
      ),
      iconButtonTheme: IconButtonThemeData(
        style: IconButton.styleFrom(
          minimumSize: const Size(48, 48),
          foregroundColor: textSecondary,
        ),
      ),
      floatingActionButtonTheme: const FloatingActionButtonThemeData(
        backgroundColor: orange,
        foregroundColor: Colors.white,
      ),
      cardTheme: CardThemeData(
        color: surfaceWarm,
        elevation: 1,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(22),
          side: const BorderSide(color: borderSoft, width: .8),
        ),
      ),
      navigationBarTheme: NavigationBarThemeData(
        height: 72,
        elevation: 0,
        backgroundColor: surface,
        indicatorColor: creamStrong,
        labelTextStyle: WidgetStateProperty.resolveWith((states) => TextStyle(
          color: states.contains(WidgetState.selected) ? orangeDark : textMuted,
          fontSize: 11,
          fontWeight: states.contains(WidgetState.selected) ? FontWeight.w800 : FontWeight.w600,
        )),
        iconTheme: WidgetStateProperty.resolveWith((states) => IconThemeData(
          color: states.contains(WidgetState.selected) ? orangeDark : textMuted,
          size: 24,
        )),
      ),
      bottomNavigationBarTheme: const BottomNavigationBarThemeData(
        backgroundColor: Colors.transparent,
        selectedItemColor: orangeDark,
        unselectedItemColor: inkSoft,
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
      scaffoldBackgroundColor: background,
    );

    return base.copyWith(
      textTheme: base.textTheme.apply(
        bodyColor: cream,
        displayColor: cream,
      ),
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
        backgroundColor: Colors.transparent,
        selectedItemColor: orangeDeep,
        unselectedItemColor: inkSoft,
        showUnselectedLabels: true,
        type: BottomNavigationBarType.fixed,
      ),
    );
  }

  static const LinearGradient appGradient = LinearGradient(
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
    colors: <Color>[
      cream,
      creamStrong,
      surfaceSoft,
    ],
  );

  static BoxDecoration frameDecoration() {
    return const BoxDecoration(color: cream);
  }

  static BoxDecoration surfaceDecoration() {
    return BoxDecoration(
      borderRadius: BorderRadius.circular(22),
      border: Border.all(color: lineStrong.withOpacity(.74)),
      gradient: const LinearGradient(
        begin: Alignment.topCenter,
        end: Alignment.bottomCenter,
        colors: <Color>[paper, paperSoft],
      ),
      boxShadow: const <BoxShadow>[
        BoxShadow(
          color: Color.fromRGBO(52, 17, 0, .07),
          blurRadius: 14,
          offset: Offset(0, 4),
        ),
      ],
    );
  }

  static BoxDecoration panelDecoration() {
    return BoxDecoration(
      borderRadius: BorderRadius.circular(22),
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
    return child;
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
