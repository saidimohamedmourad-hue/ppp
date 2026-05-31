import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../../data/models/training/training_model.dart';
import '../../../data/repositories/training_repository.dart';

final _sessionApplicantsProvider = FutureProvider.family<List<TrainingApplicationModel>, String>(
  (ref, sessionId) => TrainingRepository().sessionApplicants(sessionId),
);

class SessionApplicantsScreen extends ConsumerWidget {
  const SessionApplicantsScreen({super.key, required this.sessionId});
  final String sessionId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final appsAsync = ref.watch(_sessionApplicantsProvider(sessionId));

    return Scaffold(
      appBar: AppBar(title: const Text('Candidatures')),
      body: appsAsync.when(
        data: (apps) => apps.isEmpty
            ? const Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.person_off_outlined, size: 60, color: AppColors.grey),
                    SizedBox(height: 12),
                    Text('Aucune candidature pour cette formation'),
                  ],
                ),
              )
            : ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: apps.length,
                itemBuilder: (_, i) => _ApplicantTile(app: apps[i], onStatusChanged: () => ref.invalidate(_sessionApplicantsProvider(sessionId))),
              ),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text(e.toString())),
      ),
    );
  }
}

class _ApplicantTile extends StatelessWidget {
  const _ApplicantTile({required this.app, required this.onStatusChanged});
  final TrainingApplicationModel app;
  final VoidCallback onStatusChanged;

  Color _statusColor(String status) => switch (status) {
    'pending'     => AppColors.warning,
    'reviewed'    => AppColors.primary,
    'shortlisted' => AppColors.success,
    'rejected'    => AppColors.error,
    _             => AppColors.grey,
  };

  String _statusLabel(String status) => switch (status) {
    'pending'     => 'En attente',
    'reviewed'    => 'En cours',
    'shortlisted' => 'Sélectionné',
    'rejected'    => 'Refusé',
    _             => status,
  };

  Future<void> _changeStatus(BuildContext context) async {
    final selected = await showModalBottomSheet<String>(
      context: context,
      builder: (_) => Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Padding(padding: EdgeInsets.all(16), child: Text('Changer le statut', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16))),
          for (final s in ['pending', 'reviewed', 'shortlisted', 'rejected'])
            ListTile(
              leading: CircleAvatar(backgroundColor: _statusColor(s), radius: 6),
              title: Text(_statusLabel(s)),
              onTap: () => Navigator.pop(context, s),
            ),
          const SizedBox(height: 8),
        ],
      ),
    );
    if (selected != null && selected != app.status) {
      try {
        await TrainingRepository().updateApplicationStatus(app.id, selected);
        onStatusChanged();
      } catch (e) {
        if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString()), backgroundColor: AppColors.error));
      }
    }
  }

  @override
  Widget build(BuildContext context) => Card(
    margin: const EdgeInsets.only(bottom: 10),
    child: ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      leading: CircleAvatar(
        backgroundColor: AppColors.primary.withValues(alpha: 0.1),
        child: Text(
          (app.user?.name.isNotEmpty == true) ? app.user!.name[0].toUpperCase() : '?',
          style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold),
        ),
      ),
      title: Row(
        children: [
          Expanded(child: Text(app.user?.name ?? 'Candidat inconnu', style: const TextStyle(fontWeight: FontWeight.bold), overflow: TextOverflow.ellipsis)),
          if (app.isWaitlist) ...[
            const SizedBox(width: 6),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(
                color: AppColors.gold.withValues(alpha: 0.12),
                border: Border.all(color: AppColors.gold.withValues(alpha: 0.25)),
                borderRadius: BorderRadius.circular(100),
              ),
              child: const Text('⏳ Attente', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppColors.gold)),
            ),
          ],
        ],
      ),
      subtitle: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (app.user?.email != null) Text(app.user!.email, style: const TextStyle(color: AppColors.grey, fontSize: 12)),
          const SizedBox(height: 4),
          if ((app.aiGeneratedScore ?? 0) > 0)
            Text('Score IA: ${app.aiGeneratedScore}/100', style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.w600, fontSize: 12)),
        ],
      ),
      trailing: GestureDetector(
        onTap: () => _changeStatus(context),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
          decoration: BoxDecoration(color: _statusColor(app.status).withValues(alpha: 0.15), borderRadius: BorderRadius.circular(20)),
          child: Text(_statusLabel(app.status), style: TextStyle(color: _statusColor(app.status), fontSize: 12, fontWeight: FontWeight.w600)),
        ),
      ),
    ),
  );
}
