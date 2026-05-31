import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../providers/auth_provider.dart';
import '../../../core/constants/app_colors.dart';
import '../../../data/datasources/api_client.dart';

final _dashboardProvider = FutureProvider<Map<String, dynamic>>((ref) async {
  final res = await ApiClient().dio.get('/company/dashboard');
  return res.data as Map<String, dynamic>;
});

class CompanyDashboardScreen extends ConsumerWidget {
  const CompanyDashboardScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authProvider).valueOrNull;

    return Scaffold(
      appBar: AppBar(
        title: Text('Dashboard — ${user?.name ?? ''}'),
        actions: [
          IconButton(icon: const Icon(Icons.logout), onPressed: () async {
            await ref.read(authProvider.notifier).logout();
            if (context.mounted) context.go('/login');
          }),
        ],
      ),
      body: (user == null || !user.hasCompany)
          ? _PendingScreen(
              icon: Icons.business_outlined,
              title: 'Compte en attente',
              message: 'Votre compte a bien été créé avec le rôle entreprise.\n\nUn administrateur doit encore créer et associer votre entreprise dans le back-office.\n\nUne fois lié, rafraîchissez pour accéder à votre espace.',
              onRefresh: () async {
                await ref.read(authProvider.notifier).refreshUser();
                ref.invalidate(_dashboardProvider);
              },
            )
          : _DashboardBody(),
    );
  }
}

class _DashboardBody extends ConsumerWidget {
  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final dashAsync = ref.watch(_dashboardProvider);

    return dashAsync.when(
      data: (data) => SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            _StatsGrid(data: data),
            const SizedBox(height: 24),
            _ActionSection(),
          ],
        ),
      ),
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (e, _) => Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, size: 48, color: AppColors.error),
              const SizedBox(height: 12),
              Text(e.toString(), textAlign: TextAlign.center),
              const SizedBox(height: 16),
              ElevatedButton.icon(
                icon: const Icon(Icons.refresh),
                label: const Text('Réessayer'),
                onPressed: () => ref.invalidate(_dashboardProvider),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _PendingScreen extends StatefulWidget {
  const _PendingScreen({
    required this.icon,
    required this.title,
    required this.message,
    required this.onRefresh,
  });
  final IconData icon;
  final String title;
  final String message;
  final Future<void> Function() onRefresh;

  @override
  State<_PendingScreen> createState() => _PendingScreenState();
}

class _PendingScreenState extends State<_PendingScreen> {
  bool _refreshing = false;

  Future<void> _doRefresh() async {
    setState(() => _refreshing = true);
    await widget.onRefresh();
    if (mounted) setState(() => _refreshing = false);
  }

  @override
  Widget build(BuildContext context) => Center(
    child: Padding(
      padding: const EdgeInsets.all(32),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: AppColors.warning.withValues(alpha: 0.1),
              shape: BoxShape.circle,
            ),
            child: Icon(widget.icon, size: 64, color: AppColors.warning),
          ),
          const SizedBox(height: 24),
          Text(widget.title, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold)),
          const SizedBox(height: 12),
          Text(
            widget.message,
            textAlign: TextAlign.center,
            style: const TextStyle(color: AppColors.grey, fontSize: 15, height: 1.5),
          ),
          const SizedBox(height: 32),
          ElevatedButton.icon(
            icon: _refreshing
                ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                : const Icon(Icons.refresh),
            label: const Text('Rafraîchir'),
            onPressed: _refreshing ? null : _doRefresh,
          ),
        ],
      ),
    ),
  );
}

class _StatsGrid extends StatelessWidget {
  const _StatsGrid({required this.data});
  final Map<String, dynamic> data;

  @override
  Widget build(BuildContext context) => GridView.count(
    crossAxisCount: 2,
    shrinkWrap: true,
    physics: const NeverScrollableScrollPhysics(),
    crossAxisSpacing: 12,
    mainAxisSpacing: 12,
    childAspectRatio: 1.5,
    children: [
      _StatCard(label: 'Offres publiées', value: '${data['totalJobs'] ?? 0}', icon: Icons.work, color: AppColors.primary),
      _StatCard(label: 'Candidatures', value: '${data['totalApplications'] ?? 0}', icon: Icons.people, color: AppColors.secondary),
      _StatCard(label: 'En attente', value: '${data['pendingApplications'] ?? 0}', icon: Icons.pending, color: AppColors.warning),
      _StatCard(label: 'Vues totales', value: '-', icon: Icons.visibility, color: AppColors.success),
    ],
  );
}

class _StatCard extends StatelessWidget {
  const _StatCard({required this.label, required this.value, required this.icon, required this.color});
  final String label;
  final String value;
  final IconData icon;
  final Color color;

  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.all(16),
    decoration: BoxDecoration(color: color.withValues(alpha: 0.08), borderRadius: BorderRadius.circular(16)),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Icon(icon, color: color, size: 28),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(value, style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: color)),
            Text(label, style: const TextStyle(fontSize: 12, color: AppColors.grey)),
          ],
        ),
      ],
    ),
  );
}

class _ActionSection extends StatelessWidget {
  @override
  Widget build(BuildContext context) => Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      const Text('Actions', style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
      const SizedBox(height: 12),
      SizedBox(
        width: double.infinity,
        child: ElevatedButton.icon(icon: const Icon(Icons.add), label: const Text('Publier une offre'), onPressed: () => context.push('/company/jobs/new')),
      ),
      const SizedBox(height: 10),
      SizedBox(
        width: double.infinity,
        child: OutlinedButton.icon(icon: const Icon(Icons.list), label: const Text('Gérer mes offres'), onPressed: () => context.push('/company/jobs')),
      ),
    ],
  );
}
