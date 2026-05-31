import 'package:dio/dio.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http_mock_adapter/http_mock_adapter.dart';

import 'package:job_flutter_app/data/datasources/api_client.dart';
import 'package:job_flutter_app/data/repositories/auth_repository.dart';

/// Phase 6 — repository-level tests for the new auth flows.
///
/// We use http_mock_adapter to stub Dio so the tests never touch the network,
/// and we install a fake handler for the flutter_secure_storage platform
/// channel so token persistence calls don't throw in unit-test mode.
void main() {
  late DioAdapter adapter;
  final inMemoryStorage = <String, String>{};

  setUpAll(() {
    TestWidgetsFlutterBinding.ensureInitialized();

    // Replace flutter_secure_storage's MethodChannel with an in-memory map.
    const channel = MethodChannel('plugins.it_nomads.com/flutter_secure_storage');
    TestDefaultBinaryMessengerBinding
        .instance.defaultBinaryMessenger
        .setMockMethodCallHandler(channel, (call) async {
      switch (call.method) {
        case 'read':   return inMemoryStorage[call.arguments['key']];
        case 'write':  inMemoryStorage[call.arguments['key']] = call.arguments['value'] as String; return null;
        case 'delete': inMemoryStorage.remove(call.arguments['key']); return null;
        case 'readAll':
        case 'containsKey':
        case 'deleteAll': return null;
        default: return null;
      }
    });
  });

  setUp(() {
    inMemoryStorage.clear();
    // Hook the mock adapter into the Dio singleton used by every repo.
    adapter = DioAdapter(dio: ApiClient().dio);
  });

  // ── forgotPassword ─────────────────────────────────────────────────────

  test('forgotPassword posts the email and resolves on 200', () async {
    adapter.onPost(
      '/forgot-password',
      (s) => s.reply(200, {'message': 'Si un compte avec cette adresse existe...'}),
      data: {'email': 'ada@example.com'},
    );

    await expectLater(
      AuthRepository().forgotPassword('ada@example.com'),
      completes,
    );
  });

  test('forgotPassword surfaces the API error message on failure', () async {
    adapter.onPost(
      '/forgot-password',
      (s) => s.reply(422, {
        'message': 'Trop de tentatives',
        'errors': {'email': ['Trop de tentatives']},
      }),
      data: {'email': 'spam@example.com'},
    );

    await expectLater(
      AuthRepository().forgotPassword('spam@example.com'),
      throwsA(predicate((e) => e.toString().contains('Trop de tentatives'))),
    );
  });

  // ── resetPassword ──────────────────────────────────────────────────────

  test('resetPassword stores the returned token and returns the user', () async {
    adapter.onPost(
      '/reset-password',
      (s) => s.reply(200, {
        'message': 'Mot de passe réinitialisé.',
        'token': 'NEW-SANCTUM-TOKEN',
        'user': {
          'id': 'u-1', 'name': 'Ada', 'email': 'ada@example.com',
          'role': 'job-seeker',
        },
      }),
      data: {
        'email': 'ada@example.com',
        'token': 'reset-tk',
        'password': 'NEW_PASS_8chars',
        'password_confirmation': 'NEW_PASS_8chars',
      },
    );

    final result = await AuthRepository().resetPassword(
      email: 'ada@example.com',
      token: 'reset-tk',
      password: 'NEW_PASS_8chars',
    );

    expect(result.token, 'NEW-SANCTUM-TOKEN');
    expect(result.user.email, 'ada@example.com');

    // The new token was persisted to "secure" storage.
    expect(inMemoryStorage['auth_token'], 'NEW-SANCTUM-TOKEN');
  });

  test('resetPassword rejects bad tokens with the server-side message', () async {
    adapter.onPost(
      '/reset-password',
      (s) => s.reply(422, {
        'message': 'Le lien est invalide ou expiré.',
        'errors': {'email': ['Le lien est invalide ou expiré.']},
      }),
      data: {
        'email':    'x@y.com',
        'token':    'bad',
        'password': 'whatever8chars',
        'password_confirmation': 'whatever8chars',
      },
    );

    await expectLater(
      AuthRepository().resetPassword(
        email:    'x@y.com',
        token:    'bad',
        password: 'whatever8chars',
      ),
      throwsA(predicate((e) => e.toString().contains('invalide'))),
    );
  });

  // ── Connection errors ──────────────────────────────────────────────────

  test('connection errors are translated to a user-friendly message', () async {
    adapter.onPost(
      '/forgot-password',
      (s) => s.throws(0, DioException(
        type: DioExceptionType.connectionError,
        requestOptions: RequestOptions(path: '/forgot-password'),
        message: 'connection refused',
      )),
      data: {'email': 'x@example.com'},
    );

    await expectLater(
      AuthRepository().forgotPassword('x@example.com'),
      throwsA(predicate((e) => e.toString().contains('Impossible de joindre le serveur'))),
    );
  });
}
