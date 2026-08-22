import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class StoreTheme {
  static const Color orange = Color(0xFFFF6F1F);
  static const Color orangeHover = Color(0xFFE95C0D);
  static const Color orangeDark = Color(0xFFC94700);
  static const Color orangeSoft = Color(0xFFFF9D5A);
  static const Color gold = Color(0xFFF7B801);
  static const Color goldDark = Color(0xFFDFA500);
  static const Color goldSoft = Color(0xFFFFF7D6);
  static const Color cream = Color(0xFFF5F6F8);
  static const Color creamStrong = Color(0xFFFFF3EA);
  static const Color surface = Color(0xFFFFFFFF);
  static const Color surfaceWarm = Color(0xFFFFFFFF);
  static const Color surfaceSoft = Color(0xFFFFFAF6);
  static const Color field = Color(0xFFF0F1F3);
  static const Color textPrimary = Color(0xFF25170F);
  static const Color textDark = Color(0xFF24160F);
  static const Color textSecondary = Color(0xFF68432E);
  static const Color textMuted = Color(0xFF765744);
  static const Color border = Color(0xFFE4E6EA);
  static const Color borderSoft = Color(0xFFEBEDF0);
  static const Color borderLight = Color(0xFFF3D5BD);
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

    final jakarta = GoogleFonts.plusJakartaSansTextTheme(base.textTheme);

    return base.copyWith(
      textTheme: jakarta.copyWith(
        displayLarge: jakarta.displayLarge?.copyWith(
          color: textPrimary,
          fontSize: 48,
          fontWeight: FontWeight.w800,
          letterSpacing: -1.5,
        ),
        headlineLarge: jakarta.headlineLarge?.copyWith(
          color: textPrimary,
          fontSize: 34,
          fontWeight: FontWeight.w800,
          letterSpacing: -1,
        ),
        headlineMedium: jakarta.headlineMedium?.copyWith(
          color: textPrimary,
          fontSize: 28,
          fontWeight: FontWeight.w800,
          letterSpacing: -.6,
        ),
        titleLarge: jakarta.titleLarge?.copyWith(
          color: textPrimary,
          fontSize: 22,
          fontWeight: FontWeight.w700,
        ),
        titleMedium: jakarta.titleMedium?.copyWith(
          color: textPrimary,
          fontSize: 18,
          fontWeight: FontWeight.w700,
        ),
        bodyLarge: jakarta.bodyLarge?.copyWith(
          color: textPrimary,
          fontSize: 16,
          height: 1.45,
        ),
        bodyMedium: jakarta.bodyMedium?.copyWith(
          color: textPrimary,
          fontSize: 14,
          height: 1.45,
        ),
        bodySmall: jakarta.bodySmall?.copyWith(
          color: textSecondary,
          fontSize: 12,
          height: 1.4,
        ),
        labelLarge: jakarta.labelLarge?.copyWith(
          fontSize: 14,
          fontWeight: FontWeight.w700,
        ),
      ),
      appBarTheme: AppBarTheme(
        elevation: 0,
        scrolledUnderElevation: 0,
        surfaceTintColor: Colors.transparent,
        centerTitle: false,
        backgroundColor: surface,
        foregroundColor: textPrimary,
        titleTextStyle: jakarta.titleLarge?.copyWith(
          color: textPrimary,
          fontSize: 19,
          fontWeight: FontWeight.w800,
          letterSpacing: -.3,
        ),
      ),
      snackBarTheme: SnackBarThemeData(
        behavior: SnackBarBehavior.floating,
        backgroundColor: ink,
        contentTextStyle: jakarta.bodyMedium?.copyWith(
          color: cream,
          fontWeight: FontWeight.w600,
        ),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: field,
        hintStyle: TextStyle(color: textMuted, fontWeight: FontWeight.w500),
        labelStyle: TextStyle(color: textMuted, fontWeight: FontWeight.w600),
        contentPadding: const EdgeInsets.symmetric(
          horizontal: 18,
          vertical: 16,
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(18),
          borderSide: BorderSide(color: border.withOpacity(.7)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(18),
          borderSide: BorderSide(color: border.withOpacity(.7)),
        ),
        focusedBorder: const OutlineInputBorder(
          borderRadius: BorderRadius.all(Radius.circular(18)),
          borderSide: BorderSide(color: orange, width: 1.8),
        ),
        errorBorder: const OutlineInputBorder(
          borderRadius: BorderRadius.all(Radius.circular(18)),
          borderSide: BorderSide(color: danger, width: 1.4),
        ),
        focusedErrorBorder: const OutlineInputBorder(
          borderRadius: BorderRadius.all(Radius.circular(18)),
          borderSide: BorderSide(color: danger, width: 2),
        ),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          minimumSize: const Size(48, 54),
          backgroundColor: primary,
          foregroundColor: Colors.white,
          disabledBackgroundColor: borderSoft,
          disabledForegroundColor: textMuted,
          elevation: 0,
          padding: const EdgeInsets.symmetric(horizontal: 22),
          textStyle: jakarta.labelLarge?.copyWith(
            fontSize: 15.5,
            fontWeight: FontWeight.w800,
            letterSpacing: .1,
          ),
          shape: const StadiumBorder(),
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          minimumSize: const Size(48, 54),
          backgroundColor: orange,
          foregroundColor: Colors.white,
          elevation: 3,
          shadowColor: orange.withOpacity(.38),
          padding: const EdgeInsets.symmetric(horizontal: 22),
          textStyle: jakarta.labelLarge?.copyWith(
            fontSize: 15.5,
            fontWeight: FontWeight.w800,
            letterSpacing: .1,
          ),
          shape: const StadiumBorder(),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          minimumSize: const Size(48, 54),
          foregroundColor: primaryDark,
          side: BorderSide(color: border.withOpacity(.9), width: 1.4),
          padding: const EdgeInsets.symmetric(horizontal: 20),
          textStyle: jakarta.labelLarge?.copyWith(
            fontSize: 15,
            fontWeight: FontWeight.w800,
          ),
          shape: const StadiumBorder(),
        ),
      ),
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          minimumSize: const Size(48, 48),
          foregroundColor: orangeDark,
          textStyle: jakarta.labelLarge?.copyWith(
            fontSize: 14.5,
            fontWeight: FontWeight.w800,
          ),
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
        elevation: 4,
      ),
      cardTheme: CardThemeData(
        color: surface,
        elevation: 0,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
      ),
      navigationBarTheme: NavigationBarThemeData(
        height: 76,
        elevation: 0,
        backgroundColor: surface,
        indicatorColor: orangeSoft.withOpacity(.22),
        indicatorShape: const StadiumBorder(),
        labelTextStyle: WidgetStateProperty.resolveWith(
          (states) => TextStyle(
            color: states.contains(WidgetState.selected)
                ? orangeDark
                : textMuted,
            fontSize: 11.5,
            fontWeight: states.contains(WidgetState.selected)
                ? FontWeight.w800
                : FontWeight.w600,
          ),
        ),
        iconTheme: WidgetStateProperty.resolveWith(
          (states) => IconThemeData(
            color: states.contains(WidgetState.selected)
                ? orangeDark
                : textMuted,
            size: states.contains(WidgetState.selected) ? 26 : 24,
          ),
        ),
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
      textTheme: base.textTheme.apply(bodyColor: cream, displayColor: cream),
      snackBarTheme: const SnackBarThemeData(
        behavior: SnackBarBehavior.floating,
        backgroundColor: ink,
        contentTextStyle: TextStyle(color: cream),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: paper,
        hintStyle: const TextStyle(color: inkSoft),
        contentPadding: const EdgeInsets.symmetric(
          horizontal: 16,
          vertical: 14,
        ),
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
    colors: <Color>[cream, creamStrong, surfaceSoft],
  );

  static BoxDecoration frameDecoration() {
    return const BoxDecoration(color: cream);
  }

  static BoxDecoration surfaceDecoration() {
    return BoxDecoration(
      borderRadius: BorderRadius.circular(24),
      color: surface,
      boxShadow: const <BoxShadow>[
        BoxShadow(
          color: Color.fromRGBO(25, 22, 20, .07),
          blurRadius: 22,
          offset: Offset(0, 10),
        ),
        BoxShadow(
          color: Color.fromRGBO(25, 22, 20, .03),
          blurRadius: 4,
          offset: Offset(0, 1),
        ),
      ],
    );
  }

  static BoxDecoration panelDecoration() {
    return BoxDecoration(borderRadius: BorderRadius.circular(22), color: field);
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
