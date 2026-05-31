import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../data/models/user/user_model.dart';
import '../presentation/providers/auth_provider.dart';
import '../presentation/screens/auth/login_screen.dart';
import '../presentation/screens/auth/register_screen.dart';
import '../presentation/screens/auth/forgot_password_screen.dart';
import '../presentation/screens/auth/reset_password_screen.dart';
import '../presentation/screens/candidate/home_screen.dart';
import '../presentation/screens/candidate/job_list_screen.dart';
import '../presentation/screens/candidate/job_detail_screen.dart';
import '../presentation/screens/candidate/training_list_screen.dart';
import '../presentation/screens/candidate/training_detail_screen.dart';
import '../presentation/screens/candidate/my_applications_screen.dart';
import '../presentation/screens/candidate/my_cvs_screen.dart';
import '../presentation/screens/candidate/notifications_screen.dart';
import '../presentation/screens/candidate/profile_screen.dart';
import '../presentation/screens/candidate/linked_accounts_screen.dart';
import '../presentation/screens/candidate/settings_screen.dart';
import '../presentation/screens/company/company_dashboard_screen.dart';
import '../presentation/screens/company/company_jobs_screen.dart';
import '../presentation/screens/company/job_form_screen.dart';
import '../presentation/screens/company/job_applicants_screen.dart';
import '../presentation/screens/school/school_dashboard_screen.dart';
import '../presentation/screens/school/school_sessions_screen.dart';
import '../presentation/screens/school/session_form_screen.dart';
import '../presentation/screens/school/session_applicants_screen.dart';
import '../presentation/screens/common/splash_screen.dart';

class _AuthRouterNotifier extends ChangeNotifier {
  _AuthRouterNotifier(Ref ref) {
    ref.listen<AsyncValue<UserModel?>>(authProvider, (_, __) => notifyListeners());
  }
}

final routerProvider = Provider<GoRouter>((ref) {
  final notifier = _AuthRouterNotifier(ref);

  return GoRouter(
    initialLocation: '/splash',
    refreshListenable: notifier,
    redirect: (context, state) {
      final authState = ref.read(authProvider);
      final isLoading = authState.isLoading;
      final user = authState.valueOrNull;
      final isAuth = user != null;
      final loc = state.matchedLocation;
      final isOnAuth = loc == '/login' ||
          loc == '/register' ||
          loc == '/forgot-password' ||
          loc.startsWith('/reset-password');
      final isOnSplash = loc == '/splash';

      if (isLoading && !isOnAuth) return '/splash';
      if (!isAuth && !isOnAuth && !isOnSplash) return '/login';
      if (!isAuth && isOnSplash && !isLoading) return '/login';
      if (isAuth && (isOnAuth || isOnSplash)) {
        if (user.isJobSeeker) return '/home';
        if (user.isCompany) return '/company';
        if (user.isSchoolOwner) return '/school';
        return '/home';
      }
      return null;
    },
    routes: [
      GoRoute(path: '/splash', builder: (_, __) => const SplashScreen()),
      GoRoute(path: '/login', builder: (_, __) => const LoginScreen()),
      GoRoute(path: '/register', builder: (_, __) => const RegisterScreen()),
      GoRoute(path: '/forgot-password', builder: (_, __) => const ForgotPasswordScreen()),
      // Password reset is reached via the magic link in the email — token and
      // email arrive as query string params (?token=...&email=...).
      GoRoute(
        path: '/reset-password',
        builder: (_, s) => ResetPasswordScreen(
          token: s.uri.queryParameters['token'] ?? '',
          email: s.uri.queryParameters['email'] ?? '',
        ),
      ),

      // Candidate
      GoRoute(path: '/home', builder: (_, __) => const HomeScreen()),
      GoRoute(path: '/jobs', builder: (_, __) => const JobListScreen()),
      GoRoute(path: '/jobs/:id', builder: (_, s) => JobDetailScreen(jobId: s.pathParameters['id']!)),
      GoRoute(path: '/training', builder: (_, __) => const TrainingListScreen()),
      GoRoute(path: '/training/:id', builder: (_, s) => TrainingDetailScreen(sessionId: s.pathParameters['id']!)),
      GoRoute(path: '/my-applications', builder: (_, __) => const MyApplicationsScreen()),
      GoRoute(path: '/my-cvs', builder: (_, __) => const MyCvsScreen()),
      GoRoute(path: '/notifications', builder: (_, __) => const NotificationsScreen()),
      GoRoute(path: '/settings', builder: (_, __) => const SettingsScreen()),
      GoRoute(path: '/profile', builder: (_, __) => const ProfileScreen()),
      GoRoute(path: '/profile/linked-accounts', builder: (_, __) => const LinkedAccountsScreen()),

      // Company
      GoRoute(path: '/company', builder: (_, __) => const CompanyDashboardScreen()),
      GoRoute(path: '/company/jobs', builder: (_, __) => const CompanyJobsScreen()),
      GoRoute(path: '/company/jobs/new', builder: (_, __) => const JobFormScreen()),
      GoRoute(path: '/company/jobs/:id/edit', builder: (_, s) => JobFormScreen(jobId: s.pathParameters['id'])),
      GoRoute(path: '/company/jobs/:id/applicants', builder: (_, s) => JobApplicantsScreen(jobId: s.pathParameters['id']!)),

      // School
      GoRoute(path: '/school', builder: (_, __) => const SchoolDashboardScreen()),
      GoRoute(path: '/school/sessions', builder: (_, __) => const SchoolSessionsScreen()),
      GoRoute(path: '/school/sessions/new', builder: (_, __) => const SessionFormScreen()),
      GoRoute(path: '/school/sessions/:id/edit', builder: (_, s) => SessionFormScreen(sessionId: s.pathParameters['id'])),
      GoRoute(path: '/school/sessions/:id/applicants', builder: (_, s) => SessionApplicantsScreen(sessionId: s.pathParameters['id']!)),
    ],
  );
});
