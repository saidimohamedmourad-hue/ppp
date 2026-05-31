import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../providers/job_provider.dart';
import '../../../core/constants/app_colors.dart';
import '../../../data/models/job/job_model.dart';
import '../../../data/repositories/job_repository.dart';

class CompanyJobsScreen extends ConsumerWidget {
  const CompanyJobsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final jobsAsync = ref.watch(companyJobsProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Mes offres'),
        actions: [
          IconButton(icon: const Icon(Icons.add), onPressed: () => context.push('/company/jobs/new')),
        ],
      ),
      body: jobsAsync.when(
        data: (jobs) => jobs.isEmpty
            ? Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.work_off, size: 60, color: AppColors.grey),
                    const SizedBox(height: 12),
                    const Text('Aucune offre publiée'),
                    const SizedBox(height: 16),
                    ElevatedButton(onPressed: () => context.push('/company/jobs/new'), child: const Text('Créer une offre')),
                  ],
                ),
              )
            : ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: jobs.length,
                itemBuilder: (_, i) => _CompanyJobTile(job: jobs[i], onDeleted: () => ref.invalidate(companyJobsProvider)),
              ),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text(e.toString())),
      ),
    );
  }
}

class _CompanyJobTile extends StatelessWidget {
  const _CompanyJobTile({required this.job, required this.onDeleted});
  final JobModel job;
  final VoidCallback onDeleted;

  Future<void> _delete(BuildContext context) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Supprimer l\'offre ?'),
        content: Text('Supprimer "${job.title}" ?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Annuler')),
          TextButton(onPressed: () => Navigator.pop(context, true), child: const Text('Supprimer', style: TextStyle(color: AppColors.error))),
        ],
      ),
    );
    if (confirm == true) {
      await JobRepository().deleteJob(job.id);
      onDeleted();
    }
  }

  @override
  Widget build(BuildContext context) => Card(
    margin: const EdgeInsets.only(bottom: 10),
    child: ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      title: Text(job.title, style: const TextStyle(fontWeight: FontWeight.bold)),
      subtitle: Text('${job.location} • ${job.type}'),
      trailing: PopupMenuButton(
        itemBuilder: (_) => [
          PopupMenuItem(value: 'applicants', child: const Text('Voir candidatures'), onTap: () => context.push('/company/jobs/${job.id}/applicants')),
          PopupMenuItem(value: 'edit', child: const Text('Modifier'), onTap: () => context.push('/company/jobs/${job.id}/edit')),
          PopupMenuItem(value: 'delete', child: const Text('Supprimer', style: TextStyle(color: AppColors.error)), onTap: () => _delete(context)),
        ],
      ),
    ),
  );
}
