import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../constants/app_colors.dart';

class AppTheme {
  static ThemeData get dark => ThemeData(
    useMaterial3: true,
    brightness: Brightness.dark,
    scaffoldBackgroundColor: AppColors.bg,
    canvasColor: AppColors.bg,
    fontFamily: 'Poppins',

    colorScheme: const ColorScheme.dark(
      primary: AppColors.mint,
      onPrimary: AppColors.bg,
      secondary: AppColors.cyan,
      onSecondary: AppColors.bg,
      surface: AppColors.surface,
      onSurface: AppColors.text,
      surfaceContainerHighest: AppColors.surface2,
      error: AppColors.error,
      onError: Colors.white,
      outline: AppColors.border,
    ),

    appBarTheme: const AppBarTheme(
      elevation: 0,
      centerTitle: false,
      backgroundColor: AppColors.bg,
      foregroundColor: AppColors.text,
      systemOverlayStyle: SystemUiOverlayStyle(
        statusBarColor: Colors.transparent,
        statusBarIconBrightness: Brightness.light,
        statusBarBrightness: Brightness.dark,
      ),
      titleTextStyle: TextStyle(
        color: AppColors.text,
        fontFamily: 'Poppins',
        fontSize: 18,
        fontWeight: FontWeight.w700,
      ),
      iconTheme: IconThemeData(color: AppColors.text),
    ),

    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: AppColors.mint,
        foregroundColor: AppColors.bg,
        minimumSize: const Size(double.infinity, 52),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        textStyle: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700, letterSpacing: 0.2),
        elevation: 0,
        shadowColor: Colors.transparent,
      ),
    ),

    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: AppColors.text,
        side: const BorderSide(color: AppColors.border, width: 1),
        minimumSize: const Size(double.infinity, 52),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        textStyle: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600),
      ),
    ),

    textButtonTheme: TextButtonThemeData(
      style: TextButton.styleFrom(
        foregroundColor: AppColors.mint,
        textStyle: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
      ),
    ),

    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: AppColors.surface2,
      hintStyle: const TextStyle(color: AppColors.muted2, fontSize: 14),
      labelStyle: const TextStyle(color: AppColors.muted, fontSize: 13),
      floatingLabelStyle: const TextStyle(color: AppColors.mint, fontSize: 13),
      prefixIconColor: AppColors.muted,
      suffixIconColor: AppColors.muted,
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: AppColors.border, width: 1),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: AppColors.border, width: 1),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: AppColors.mint, width: 1.5),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: AppColors.error, width: 1),
      ),
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
    ),

    cardTheme: CardThemeData(
      elevation: 0,
      margin: EdgeInsets.zero,
      color: AppColors.surface,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: const BorderSide(color: AppColors.border, width: 1),
      ),
    ),

    chipTheme: ChipThemeData(
      backgroundColor: AppColors.surface2,
      selectedColor: AppColors.mint.withValues(alpha: 0.15),
      labelStyle: const TextStyle(color: AppColors.text, fontSize: 12, fontWeight: FontWeight.w500),
      secondaryLabelStyle: const TextStyle(color: AppColors.mint, fontSize: 12, fontWeight: FontWeight.w600),
      side: const BorderSide(color: AppColors.border, width: 1),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(100)),
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
    ),

    dividerTheme: const DividerThemeData(
      color: AppColors.border,
      thickness: 1,
      space: 1,
    ),

    listTileTheme: const ListTileThemeData(
      textColor: AppColors.text,
      iconColor: AppColors.muted,
      tileColor: Colors.transparent,
    ),

    navigationBarTheme: NavigationBarThemeData(
      backgroundColor: AppColors.surface,
      indicatorColor: AppColors.mint.withValues(alpha: 0.15),
      surfaceTintColor: Colors.transparent,
      height: 68,
      labelTextStyle: WidgetStateProperty.resolveWith((states) {
        final selected = states.contains(WidgetState.selected);
        return TextStyle(
          color: selected ? AppColors.mint : AppColors.muted,
          fontSize: 11,
          fontWeight: selected ? FontWeight.w700 : FontWeight.w500,
        );
      }),
      iconTheme: WidgetStateProperty.resolveWith((states) {
        final selected = states.contains(WidgetState.selected);
        return IconThemeData(color: selected ? AppColors.mint : AppColors.muted, size: 22);
      }),
    ),

    bottomSheetTheme: const BottomSheetThemeData(
      backgroundColor: AppColors.surface,
      surfaceTintColor: Colors.transparent,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
    ),

    dialogTheme: DialogThemeData(
      backgroundColor: AppColors.surface,
      surfaceTintColor: Colors.transparent,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(20),
        side: const BorderSide(color: AppColors.border, width: 1),
      ),
      titleTextStyle: const TextStyle(color: AppColors.text, fontSize: 18, fontWeight: FontWeight.w700, fontFamily: 'Poppins'),
      contentTextStyle: const TextStyle(color: AppColors.muted, fontSize: 14, fontFamily: 'Poppins'),
    ),

    snackBarTheme: SnackBarThemeData(
      backgroundColor: AppColors.surface2,
      contentTextStyle: const TextStyle(color: AppColors.text, fontSize: 14),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      behavior: SnackBarBehavior.floating,
      actionTextColor: AppColors.mint,
    ),

    progressIndicatorTheme: const ProgressIndicatorThemeData(
      color: AppColors.mint,
      linearTrackColor: AppColors.border,
      circularTrackColor: AppColors.border,
    ),

    tabBarTheme: const TabBarThemeData(
      labelColor: AppColors.mint,
      unselectedLabelColor: AppColors.muted,
      indicatorColor: AppColors.mint,
      dividerColor: AppColors.border,
      labelStyle: TextStyle(fontWeight: FontWeight.w700, fontSize: 13),
      unselectedLabelStyle: TextStyle(fontWeight: FontWeight.w500, fontSize: 13),
    ),

    textTheme: const TextTheme(
      displayLarge: TextStyle(color: AppColors.text, fontWeight: FontWeight.w800),
      displayMedium: TextStyle(color: AppColors.text, fontWeight: FontWeight.w800),
      displaySmall: TextStyle(color: AppColors.text, fontWeight: FontWeight.w800),
      headlineLarge: TextStyle(color: AppColors.text, fontWeight: FontWeight.w700),
      headlineMedium: TextStyle(color: AppColors.text, fontWeight: FontWeight.w700),
      headlineSmall: TextStyle(color: AppColors.text, fontWeight: FontWeight.w700),
      titleLarge: TextStyle(color: AppColors.text, fontWeight: FontWeight.w700),
      titleMedium: TextStyle(color: AppColors.text, fontWeight: FontWeight.w600),
      titleSmall: TextStyle(color: AppColors.text, fontWeight: FontWeight.w600),
      bodyLarge: TextStyle(color: AppColors.text),
      bodyMedium: TextStyle(color: AppColors.text),
      bodySmall: TextStyle(color: AppColors.muted),
      labelLarge: TextStyle(color: AppColors.text, fontWeight: FontWeight.w600),
      labelMedium: TextStyle(color: AppColors.muted, fontWeight: FontWeight.w500),
      labelSmall: TextStyle(color: AppColors.muted2, fontWeight: FontWeight.w500),
    ),

    iconTheme: const IconThemeData(color: AppColors.muted, size: 22),
  );

  // Kept for backward compatibility — points to the new dark theme.
  static ThemeData get light => dark;
}
