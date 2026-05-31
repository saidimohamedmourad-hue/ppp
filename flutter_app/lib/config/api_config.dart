import 'package:flutter/foundation.dart';

/// URL de l'API.
///
/// - **Release (stores)** : obligatoire `--dart-define=API_BASE_URL=https://votre-domaine.com/api`.
/// - **Debug** : détection automatique selon la plateforme :
///   - Émulateur Android  → `http://10.0.2.2:8000/api` (alias du localhost du PC)
///   - Web (Chrome) / iOS simulator / desktop → `http://localhost:8000/api`
///   - Téléphone physique → utiliser `--dart-define=API_BASE_URL_DEV=http://<IP-PC>:8000/api`
///     (et démarrer Laravel avec `php artisan serve --host=0.0.0.0`)
class ApiConfig {
  static String get baseUrl {
    // 1) Override explicite (prioritaire dans tous les modes)
    const overrideUrl = String.fromEnvironment('API_BASE_URL');
    if (overrideUrl.isNotEmpty) return overrideUrl;

    if (kReleaseMode) {
      throw StateError(
        'Build release sans API_BASE_URL. Exemple :\n'
        'flutter build appbundle --dart-define=API_BASE_URL=https://api.example.com/api',
      );
    }

    // 2) Override de dev (si défini)
    const devOverride = String.fromEnvironment('API_BASE_URL_DEV');
    if (devOverride.isNotEmpty) return devOverride;

    // 3) Auto-détection plateforme en debug (compatible Flutter web)
    if (kIsWeb) return 'http://localhost:8000/api';
    if (defaultTargetPlatform == TargetPlatform.android) {
      return 'http://10.0.2.2:8000/api';
    }
    // iOS simulator, macOS, Windows, Linux
    return 'http://localhost:8000/api';
  }

  static const Duration connectTimeout = Duration(seconds: 15);
  static const Duration receiveTimeout = Duration(seconds: 30);
}
