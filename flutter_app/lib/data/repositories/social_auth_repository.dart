import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_facebook_auth/flutter_facebook_auth.dart';
import 'package:google_sign_in/google_sign_in.dart';
import '../datasources/api_client.dart';
import '../models/user/user_model.dart';

/// Configures the Google OAuth flow. `serverClientId` is our WEB OAuth client
/// ID — passing it here makes Google issue an ID token whose `aud` matches
/// the backend's `GOOGLE_WEB_CLIENT_ID`, which is what the backend will check
/// when verifying the token.
///
/// On Android / iOS the SDK additionally needs the platform-specific client
/// ID configured via google-services.json / GoogleService-Info.plist — those
/// are set up natively and don't appear in Dart code.
GoogleSignIn _buildGoogleSignIn() {
  const serverClientId = String.fromEnvironment('GOOGLE_SERVER_CLIENT_ID');
  return GoogleSignIn(
    scopes: const ['email', 'profile', 'openid'],
    serverClientId: serverClientId.isNotEmpty ? serverClientId : null,
  );
}

class SocialAuthRepository {
  final _client = ApiClient();

  /// Runs the native Google sign-in flow, then exchanges the resulting ID
  /// token with our backend for a Sanctum token.
  ///
  /// Returns `null` if the user cancelled the dialog (we treat that as a
  /// silent no-op rather than an error).
  Future<({UserModel user, String token})?> signInWithGoogle({String? role}) async {
    final googleSignIn = _buildGoogleSignIn();
    try {
      final account = await googleSignIn.signIn();
      if (account == null) return null; // user cancelled

      return await completeGoogleFromAccount(account, role: role);
    } on DioException catch (e) {
      // Clean up the Google session so a retry doesn't reuse a stale grant.
      await googleSignIn.signOut().catchError((_) => null);
      throw Exception(_extractError(e));
    } catch (e) {
      await googleSignIn.signOut().catchError((_) => null);
      if (kDebugMode) debugPrint('signInWithGoogle: $e');
      rethrow;
    }
  }

  /// Exchanges an already-authenticated [GoogleSignInAccount] for a Sanctum
  /// token. Used by the web GIS button flow, where the account arrives through
  /// `GoogleSignIn.onCurrentUserChanged` rather than from `signIn()`.
  Future<({UserModel user, String token})> completeGoogleFromAccount(
    GoogleSignInAccount account, {
    String? role,
  }) async {
    final auth = await account.authentication;
    return _exchangeGoogle(idToken: auth.idToken, accessToken: auth.accessToken, role: role);
  }

  /// POSTs the Google token to the backend and persists the returned Sanctum
  /// token. Prefers the signed ID token (cheapest to verify); falls back to an
  /// opaque access_token (web implicit flow) when no ID token was issued.
  Future<({UserModel user, String token})> _exchangeGoogle({
    String? idToken,
    String? accessToken,
    String? role,
  }) async {
    final body = <String, dynamic>{};
    if (idToken != null && idToken.isNotEmpty) {
      body['id_token'] = idToken;
    } else if (accessToken != null && accessToken.isNotEmpty) {
      body['access_token'] = accessToken;
    } else {
      throw Exception('Google n\'a pas retourné de jeton utilisable.');
    }
    // Rôle souhaité à l'inscription (appliqué seulement si nouveau compte).
    if (role != null && role.isNotEmpty) body['role'] = role;

    final res = await _client.dio.post('/auth/google', data: body);
    final apiToken = res.data['token'] as String;
    await _client.setToken(apiToken);
    return (
      user: UserModel.fromJson(res.data['user'] as Map<String, dynamic>),
      token: apiToken,
    );
  }

  /// Runs the native Facebook Login flow, then exchanges the access token
  /// with our backend for a Sanctum token.
  ///
  /// Instagram users with a linked Facebook account can sign in here too.
  /// Returns `null` if the user cancelled the dialog.
  Future<({UserModel user, String token})?> signInWithFacebook({String? role}) async {
    try {
      final result = await FacebookAuth.instance.login(
        permissions: const ['email', 'public_profile'],
      );

      if (result.status == LoginStatus.cancelled) return null;
      if (result.status != LoginStatus.success || result.accessToken == null) {
        throw Exception(result.message ?? 'Connexion Facebook échouée.');
      }

      final accessToken = result.accessToken!.tokenString;
      final res = await _client.dio.post('/auth/facebook', data: {
        'access_token': accessToken,
        if (role != null && role.isNotEmpty) 'role': role,
      });

      final apiToken = res.data['token'] as String;
      await _client.setToken(apiToken);
      return (
        user: UserModel.fromJson(res.data['user'] as Map<String, dynamic>),
        token: apiToken,
      );
    } on DioException catch (e) {
      // Clean up the FB session so a retry doesn't reuse a stale grant.
      await FacebookAuth.instance.logOut().catchError((_) {});
      throw Exception(_extractError(e));
    } catch (e) {
      await FacebookAuth.instance.logOut().catchError((_) {});
      if (kDebugMode) debugPrint('signInWithFacebook: $e');
      rethrow;
    }
  }

  // ─── Phase 5 — manage linked providers from the profile ───────────────

  /// Returns { has_password, providers: [...] } for the current user.
  Future<({bool hasPassword, List<LinkedProvider> providers})> listProviders() async {
    final res = await _client.dio.get('/profile/auth-providers');
    final data = res.data as Map<String, dynamic>;
    return (
      hasPassword: data['has_password'] as bool? ?? false,
      providers: (data['providers'] as List)
          .map((j) => LinkedProvider.fromJson(j as Map<String, dynamic>))
          .toList(),
    );
  }

  /// Runs Google Sign-In once and POSTs the resulting access token to the
  /// link endpoint. Returns null if the user cancelled.
  Future<bool> linkGoogle() async {
    final google = GoogleSignIn(scopes: const ['email', 'profile']);
    await google.signOut();
    final account = await google.signIn();
    if (account == null) return false;
    final auth = await account.authentication;
    try {
      await _client.dio.post('/profile/auth-providers/google', data: {
        if (auth.idToken != null) 'id_token': auth.idToken,
        if (auth.accessToken != null) 'access_token': auth.accessToken,
      });
      return true;
    } on DioException catch (e) {
      throw Exception(_extractError(e));
    }
  }

  Future<bool> linkFacebook() async {
    final r = await FacebookAuth.instance.login(permissions: const ['email', 'public_profile']);
    if (r.status == LoginStatus.cancelled) return false;
    if (r.status != LoginStatus.success || r.accessToken == null) {
      throw Exception(r.message ?? 'Connexion Facebook échouée.');
    }
    try {
      await _client.dio.post('/profile/auth-providers/facebook', data: {
        'access_token': r.accessToken!.tokenString,
      });
      return true;
    } on DioException catch (e) {
      await FacebookAuth.instance.logOut().catchError((_) {});
      throw Exception(_extractError(e));
    }
  }

  Future<void> unlinkProvider(String providerId) async {
    try {
      await _client.dio.delete('/profile/auth-providers/$providerId');
    } on DioException catch (e) {
      throw Exception(_extractError(e));
    }
  }

  /// Sets an initial password on a social-only account, or changes the
  /// existing one when [currentPassword] is provided.
  Future<void> setPassword({String? currentPassword, required String password}) async {
    try {
      await _client.dio.post('/profile/password-init', data: {
        if (currentPassword != null) 'current_password': currentPassword,
        'password': password,
        'password_confirmation': password,
      });
    } on DioException catch (e) {
      throw Exception(_extractError(e));
    }
  }

  String _extractError(DioException e) {
    final data = e.response?.data;
    if (data is Map) {
      if (data['message'] != null) return data['message'].toString();
      final errors = data['errors'];
      if (errors is Map && errors.isNotEmpty) {
        return (errors.values.first as List).first.toString();
      }
    }
    return 'Connexion Google échouée. Réessayez.';
  }
}

/// Phase 5 DTO — one row from /api/profile/auth-providers.
class LinkedProvider {
  LinkedProvider({
    required this.id,
    required this.provider,
    required this.displayId,
    required this.linkedAt,
  });

  factory LinkedProvider.fromJson(Map<String, dynamic> json) => LinkedProvider(
    id: json['id'] as String,
    provider: json['provider'] as String,
    displayId: json['display_id'] as String? ?? '',
    linkedAt: DateTime.tryParse(json['linked_at'] as String? ?? '') ?? DateTime.now(),
  );

  final String id;
  final String provider; // 'google' | 'facebook'
  final String displayId;
  final DateTime linkedAt;
}
