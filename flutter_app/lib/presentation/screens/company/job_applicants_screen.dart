import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../providers/job_provider.dart';
import '../../../core/constants/app_colors.dart';
import '../../../data/models/job/job_model.dart';
import '../../../data/repositories/job_repository.dart';

class JobApplicantsScreen extends ConsumerWidget {
  const JobApplicantsScreen({super.key, required this.jobId});
  final String jobId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final appsAsync = ref.watch(jobApplicantsProvider(jobId));

    return Scaffold(
      appBar: AppBar(title: const Text('Candidatures reçues')),
      body: appsAsync.when(
        data: (apps) => apps.isEmpty
            ? const Center(child: Text('Aucune candidature'))
            : ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: apps.length,
                itemBuilder: (_, i) => _ApplicantTile(
                  app: apps[i],
                  onStatusChanged: () => ref.invalidate(jobApplicantsProvider(jobId)),
                ),
              ),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text(e.toString())),
      ),
    );
  }
}

class _ApplicantTile extends StatelessWidget {
  const _ApplicantTile({required this.app, required this.onStatusChanged});
  final JobApplicationModel app;
  final VoidCallback onStatusChanged;

  Color get _statusColor => switch (app.status) {
    'shortlisted' => AppColors.success,
    'rejected'    => AppColors.error,
    'reviewed'    => AppColors.warning,
    _             => AppColors.grey,
  };

  Future<void> _updateStatus(BuildContext context, String status) async {
    try {
      await JobRepository().updateApplicationStatus(app.id, status);
      onStatusChanged();
    } catch (e) {
      if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  @override
  Widget build(BuildContext context) => Card(
    margin: const EdgeInsets.only(bottom: 10),
    child: Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              CircleAvatar(backgroundColor: AppColors.primary.withValues(alpha: 0.1), child: const Icon(Icons.person, color: AppColors.primary)),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(app.user?.name ?? 'Candidat inconnu', style: const TextStyle(fontWeight: FontWeight.bold)),
                    if (app.user?.email != null)
                      Text(app.user!.email, style: const TextStyle(color: AppColors.grey, fontSize: 12)),
                    if ((app.aiGeneratedScore ?? 0) > 0)
                      Text('Score IA: ${app.aiGeneratedScore}/100', style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.w600, fontSize: 13)),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(color: _statusColor.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(8)),
                child: Text(app.status, style: TextStyle(color: _statusColor, fontWeight: FontWeight.w600, fontSize: 12)),
              ),
            ],
          ),
          if (app.aiGeneratedFeedback?.isNotEmpty == true) ...[
            const SizedBox(height: 10),
            Text(app.aiGeneratedFeedback!, style: const TextStyle(fontSize: 13, color: AppColors.grey)),
          ],
          const SizedBox(height: 12),
          Row(
            children: [
              _StatusBtn(label: 'Accepter', color: AppColors.success, onTap: () => _updateStatus(context, 'shortlisted')),
              const SizedBox(width: 8),
              _StatusBtn(label: 'Refuser', color: AppColors.error, onTap: () => _updateStatus(context, 'rejected')),
              const SizedBox(width: 8),
              _StatusBtn(label: 'Revu', color: AppColors.warning, onTap: () => _updateStatus(context, 'reviewed')),
            ],
          ),
        ],
      ),
    ),
  );
}

class _StatusBtn extends StatelessWidget {
  const _StatusBtn({required this.label, required this.color, required this.onTap});
  final String label;
  final Color color;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) => GestureDetector(
    onTap: onTap,
    child: Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(color: color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(8), border: Border.all(color: color.withValues(alpha: 0.3))),
      child: Text(label, style: TextStyle(color: color, fontWeight: FontWeight.w600, fontSize: 12)),
    ),
  );
}
