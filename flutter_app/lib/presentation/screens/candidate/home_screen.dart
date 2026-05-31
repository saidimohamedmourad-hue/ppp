import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../providers/auth_provider.dart';
import '../../providers/job_provider.dart';
import '../../providers/training_provider.dart';
import '../../../core/constants/app_colors.dart';
import '../../../data/models/job/job_model.dart';
import '../../../data/models/training/training_model.dart';

class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authProvider).valueOrNull;
    final jobsAsync = ref.watch(jobListProvider((search: null, type: null, category: null)));
    final trainingAsync = ref.watch(trainingListProvider((search: null, category: null, type: null)));

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Bonjour, ${user?.name.split(' ').first ?? ''}', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            const Text('Trouvez votre opportunité', style: TextStyle(fontSize: 12, color: AppColors.grey)),
          ],
        ),
        actions: [
          IconButton(icon: const Icon(Icons.person_outline), onPressed: () => context.push('/profile')),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Quick actions
            Row(
              children: [
                _QuickAction(icon: Icons.work_outline, label: 'Emplois', color: AppColors.primary, onTap: () => context.push('/jobs')),
                const SizedBox(width: 12),
                _QuickAction(icon: Icons.school_outlined, label: 'Formations', color: AppColors.secondary, onTap: () => context.push('/training')),
                const SizedBox(width: 12),
                _QuickAction(icon: Icons.assignment_outlined, label: 'Mes candidatures', color: AppColors.success, onTap: () => context.push('/my-applications')),
              ],
            ),
            const SizedBox(height: 24),
            // Recent Jobs
            _SectionHeader(title: 'Offres récentes', onSeeAll: () => context.push('/jobs')),
            const SizedBox(height: 12),
            jobsAsync.when(
              data: (jobs) => Column(children: jobs.take(3).map((j) => _JobCard(job: j)).toList()),
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (e, _) => _ErrorCard(message: e.toString()),
            ),
            const SizedBox(height: 24),
            // Recent Training
            _SectionHeader(title: 'Formations disponibles', onSeeAll: () => context.push('/training')),
            const SizedBox(height: 12),
            trainingAsync.when(
              data: (sessions) => Column(children: sessions.take(3).map((s) => _TrainingCard(session: s)).toList()),
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (e, _) => _ErrorCard(message: e.toString()),
            ),
          ],
        ),
      ),
      bottomNavigationBar: NavigationBar(
        destinations: const [
          NavigationDestination(icon: Icon(Icons.home_outlined), selectedIcon: Icon(Icons.home), label: 'Accueil'),
          NavigationDestination(icon: Icon(Icons.work_outline), selectedIcon: Icon(Icons.work), label: 'Emplois'),
          NavigationDestination(icon: Icon(Icons.school_outlined), selectedIcon: Icon(Icons.school), label: 'Formations'),
          NavigationDestination(icon: Icon(Icons.assignment_outlined), selectedIcon: Icon(Icons.assignment), label: 'Candidatures'),
        ],
        selectedIndex: 0,
        onDestinationSelected: (i) {
          switch (i) {
            case 1: context.push('/jobs');
            case 2: context.push('/training');
            case 3: context.push('/my-applications');
          }
        },
      ),
    );
  }
}

class _QuickAction extends StatelessWidget {
  const _QuickAction({required this.icon, required this.label, required this.color, required this.onTap});
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) => Expanded(
    child: GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 16),
        decoration: BoxDecoration(color: color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(16)),
        child: Column(
          children: [
            Icon(icon, color: color, size: 28),
            const SizedBox(height: 6),
            Text(label, style: TextStyle(fontSize: 11, color: color, fontWeight: FontWeight.w600), textAlign: TextAlign.center),
          ],
        ),
      ),
    ),
  );
}

class _SectionHeader extends StatelessWidget {
  const _SectionHeader({required this.title, required this.onSeeAll});
  final String title;
  final VoidCallback onSeeAll;

  @override
  Widget build(BuildContext context) => Row(
    mainAxisAlignment: MainAxisAlignment.spaceBetween,
    children: [
      Text(title, style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
      TextButton(onPressed: onSeeAll, child: const Text('Voir tout')),
    ],
  );
}

class _JobCard extends StatelessWidget {
  const _JobCard({required this.job});
  final JobModel job;

  @override
  Widget build(BuildContext context) => Card(
    margin: const EdgeInsets.only(bottom: 10),
    child: ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      leading: CircleAvatar(backgroundColor: AppColors.primary.withValues(alpha: 0.1), child: const Icon(Icons.business, color: AppColors.primary)),
      title: Text(job.title, style: const TextStyle(fontWeight: FontWeight.w600)),
      subtitle: Text('${job.company?.name ?? ''} • ${job.location}'),
      trailing: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(color: AppColors.primary.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(8)),
            child: Text(job.type, style: const TextStyle(fontSize: 11, color: AppColors.primary, fontWeight: FontWeight.w600)),
          ),
          const SizedBox(height: 4),
          Text('${job.salary.toStringAsFixed(0)} DA', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
        ],
      ),
      onTap: () => context.push('/jobs/${job.id}'),
    ),
  );
}

class _TrainingCard extends StatelessWidget {
  const _TrainingCard({required this.session});
  final TrainingSessionModel session;

  @override
  Widget build(BuildContext context) => Card(
    margin: const EdgeInsets.only(bottom: 10),
    child: ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      leading: CircleAvatar(backgroundColor: AppColors.secondary.withValues(alpha: 0.1), child: const Icon(Icons.school, color: AppColors.secondary)),
      title: Text(session.title, style: const TextStyle(fontWeight: FontWeight.w600)),
      subtitle: Text('${session.school?.name ?? ''} • ${session.location}'),
      trailing: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text('${session.currentParticipants}/${session.maxParticipants}', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
          Text('places', style: TextStyle(fontSize: 10, color: session.isFull ? AppColors.error : AppColors.success)),
        ],
      ),
      onTap: () => context.push('/training/${session.id}'),
    ),
  );
}

class _ErrorCard extends StatelessWidget {
  const _ErrorCard({required this.message});
  final String message;

  @override
  Widget build(BuildContext context) => Card(
    color: AppColors.error.withValues(alpha: 0.05),
    child: Padding(
      padding: const EdgeInsets.all(16),
      child: Text(message, style: const TextStyle(color: AppColors.error)),
    ),
  );
}
