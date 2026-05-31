import 'package:flutter/material.dart';

class AppColors {
  // ── Midnight Mint palette ──
  static const bg       = Color(0xFF080C14); // page background
  static const surface  = Color(0xFF131A26); // cards
  static const surface2 = Color(0xFF1A2030); // raised surfaces / inputs
  static const border   = Color(0xFF1E2533); // dividers / borders

  // Accents
  static const mint     = Color(0xFF4FFFB0); // brand accent
  static const cyan     = Color(0xFF00D4FF);
  static const gold     = Color(0xFFF0C45A); // mid-score / pending status
  static const blue     = Color(0xFF60A5FA);

  // Text shades
  static const text     = Color(0xFFE6E9EE); // ~rgba(255,255,255,0.9)
  static const muted    = Color(0xFF8B9099); // ~rgba(255,255,255,0.55)
  static const muted2   = Color(0xFF6B7178); // ~rgba(255,255,255,0.42)

  // ── Semantic aliases (kept for existing screens) ──
  // NB: in the light theme, `dark` meant "primary text color (dark-on-light)".
  // In the dark theme, the primary text color is light — so `dark` now points
  // to the light text color to keep existing TextStyle(color: AppColors.dark)
  // usages legible. Use AppColors.bg explicitly if you actually need the
  // background color.
  static const primary   = mint;
  static const secondary = cyan;
  static const success   = Color(0xFF4ADE80);
  static const warning   = gold;
  static const error     = Color(0xFFEF4444);
  static const dark      = text;
  static const grey      = muted;
  static const lightGrey = surface;
  static const white     = text;
}
