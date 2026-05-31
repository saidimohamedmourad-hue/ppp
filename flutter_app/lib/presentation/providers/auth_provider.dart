import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/models/user/user_model.dart';
import '../../data/repositories/auth_repository.dart';

final authRepositoryProvider = Provider((_) => AuthRepository());

final authProvider = StateNotifierProvider<AuthNotifier, AsyncValue<UserModel?>>(
  (ref) => AuthNotifier(ref.read(authRepositoryProvider)),
);

class AuthNotifier extends StateNotifier<AsyncValue<UserModel?>> {
  AuthNotifier(this._repo) : super(const AsyncValue.loading()) {
    _init();
  }

  final AuthRepository _repo;

  Future<void> _init() async {
    state = const AsyncValue.loading();
    try {
      final user = await _repo.me();
      state = AsyncValue.data(user);
    } catch (e, s) {
      state = AsyncValue.error(e, s);
    }
  }

  Future<void> login(String email, String password) async {
    state = const AsyncValue.loading();
    try {
      final result = await _repo.login(email, password);
      state = AsyncValue.data(result.user);
    } catch (e, s) {
      state = AsyncValue.error(e, s);
      rethrow;
    }
  }

  Future<void> register({
    required String name,
    required String email,
    required String password,
    required String role,
    String? phone,
  }) async {
    state = const AsyncValue.loading();
    try {
      final result = await _repo.register(name: name, email: email, password: password, role: role, phone: phone);
      state = AsyncValue.data(result.user);
    } catch (e, s) {
      state = AsyncValue.error(e, s);
      rethrow;
    }
  }

  void setUser(UserModel user) {
    state = AsyncValue.data(user);
  }

  Future<void> refreshUser() async {
    try {
      final user = await _repo.me();
      state = AsyncValue.data(user);
    } catch (_) {}
  }

  Future<void> logout() async {
    await _repo.logout();
    state = const AsyncValue.data(null);
  }
}
