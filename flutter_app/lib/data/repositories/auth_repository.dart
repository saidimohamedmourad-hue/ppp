import 'package:dio/dio.dart';
import '../datasources/api_client.dart';
import '../models/user/user_model.dart';

String _extractError(Object e) {
  if (e is DioException) {
    final data = e.response?.data;
    if (data is Map) {
      if (data['message'] != null) return data['message'].toString();
      final errors = data['errors'];
      if (errors is Map) {
        return (errors.values.first as List).first.toString();
      }
    }
    if (e.type == DioExceptionType.connectionError ||
        e.type == DioExceptionType.connectionTimeout) {
      return 'Impossible de joindre le serveur. Vérifiez que le backend est démarré.';
    }
    if (e.type == DioExceptionType.unknown) {
      return 'Erreur réseau. Vérifiez votre connexion et que l\'API backend est accessible.';
    }
  }
  return e.toString();
}

class AuthRepository {
  final _client = ApiClient();

  Future<({UserModel user, String token})> login(String email, String password) async {
    try {
      final res = await _client.dio.post('/login', data: {'email': email, 'password': password});
      final token = res.data['token'] as String;
      await _client.setToken(token);
      return (user: UserModel.fromJson(res.data['user'] as Map<String, dynamic>), token: token);
    } on DioException catch (e) {
      throw Exception(_extractError(e));
    }
  }

  Future<({UserModel user, String token})> register({
    required String name,
    required String email,
    required String password,
    required String role,
    String? phone,
  }) async {
    try {
      final res = await _client.dio.post('/register', data: {
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': password,
        'role': role,
        if (phone != null && phone.isNotEmpty) 'phone': phone,
      });
      final token = res.data['token'] as String;
      await _client.setToken(token);
      return (user: UserModel.fromJson(res.data['user'] as Map<String, dynamic>), token: token);
    } on DioException catch (e) {
      throw Exception(_extractError(e));
    }
  }

  Future<void> logout() async {
    try {
      await _client.dio.post('/logout');
    } catch (_) {}
    await _client.clearToken();
  }

  Future<UserModel> updateProfile({String? name, String? email}) async {
    try {
      final res = await _client.dio.put('/profile', data: {
        if (name != null) 'name': name,
        if (email != null) 'email': email,
      });
      return UserModel.fromJson(res.data as Map<String, dynamic>);
    } on DioException catch (e) {
      throw Exception(_extractError(e));
    }
  }

  Future<void> updatePassword({
    required String currentPassword,
    required String newPassword,
  }) async {
    try {
      await _client.dio.put('/profile/password', data: {
        'current_password': currentPassword,
        'password': newPassword,
        'password_confirmation': newPassword,
      });
    } on DioException catch (e) {
      throw Exception(_extractError(e));
    }
  }

  /// Ask the backend to email a password-reset link.
  /// The backend returns 200 even when the email is unknown (anti-enumeration).
  Future<void> forgotPassword(String email) async {
    try {
      await _client.dio.post('/forgot-password', data: {'email': email});
    } on DioException catch (e) {
      throw Exception(_extractError(e));
    }
  }

  /// Consume a reset token and set a new password. On success we get a fresh
  /// Sanctum token back so the user is auto-logged-in.
  Future<({UserModel user, String token})> resetPassword({
    required String email,
    required String token,
    required String password,
  }) async {
    try {
      final res = await _client.dio.post('/reset-password', data: {
        'email': email,
        'token': token,
        'password': password,
        'password_confirmation': password,
      });
      final apiToken = res.data['token'] as String;
      await _client.setToken(apiToken);
      return (user: UserModel.fromJson(res.data['user'] as Map<String, dynamic>), token: apiToken);
    } on DioException catch (e) {
      throw Exception(_extractError(e));
    }
  }

  Future<UserModel?> me() async {
    final token = await _client.getToken();
    if (token == null) return null;
    try {
      final res = await _client.dio.get('/me');
      return UserModel.fromJson(res.data['user'] as Map<String, dynamic>);
    } on DioException {
      await _client.clearToken();
      return null;
    }
  }
}
