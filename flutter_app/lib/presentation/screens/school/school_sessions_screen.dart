import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../providers/training_provider.dart';
import '../../../core/constants/app_colors.dart';
import '../../../data/models/training/training_model.dart';
import '../../../data/repositories/training_repository.dart';

class SchoolSessionsScreen extends ConsumerWidget {
  const SchoolSessionsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final sessionsAsync = ref.watch(schoolSessionsProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Mes formations'),
        actions: [IconButton(icon: const Icon(Icons.add), onPressed: () => context.push('/school/sessions/new'))],
      ),
      body: sessionsAsync.when(
        data: (sessions) => sessions.isEmpty
            ? Center(
                child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                  const Icon(Icons.school_outlined, size: 60, color: AppColors.grey),
                  const SizedBox(height: 12),
                  const Text('Aucune formation'),
                  const SizedBox(height: 16),
                  ElevatedButton(onPressed: () => context.push('/school/sessions/new'), child: const Text('Créer une formation')),
                ]),
              )
            : ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: sessions.length,
                itemBuilder: (_, i) => _SessionTile(session: sessions[i], onDeleted: () => ref.invalidate(schoolSessionsProvider)),
              ),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text(e.toString())),
      ),
    );
  }
}

class _SessionTile extends StatelessWidget {
  const _SessionTile({required this.session, required this.onDeleted});
  final TrainingSessionModel session;
  final VoidCallback onDeleted;

  Color get _statusColor => switch (session.status) {
    'open'      => AppColors.success,
    'closed'    => AppColors.error,
    'cancelled' => AppColors.error,
    _           => AppColors.grey,
  };

  Future<void> _delete(BuildContext context) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Supprimer ?'),
        content: Text('Supprimer "${session.title}" ?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Annuler')),
          TextButton(onPressed: () => Navigator.pop(context, true), child: const Text('Supprimer', style: TextStyle(color: AppColors.error))),
        ],
      ),
    );
    if (ok == true) { await TrainingRepository().deleteSession(session.id); onDeleted(); }
  }

  @override
  Widget build(BuildContext context) => Card(
    margin: const EdgeInsets.only(bottom: 10),
    child: ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      title: Text(session.title, style: const TextStyle(fontWeight: FontWeight.bold)),
      subtitle: Text('${session.location} • ${session.currentParticipants}/${session.maxParticipants} places'),
      leading: Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
        decoration: BoxDecoration(color: _statusColor.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(8)),
        child: Text(session.status, style: TextStyle(color: _statusColor, fontSize: 11, fontWeight: FontWeight.w600)),
      ),
      trailing: PopupMenuButton(itemBuilder: (_) => [
        PopupMenuItem(value: 'applicants', child: const Text('Voir candidatures'), onTap: () => context.push('/school/sessions/${session.id}/applicants')),
        PopupMenuItem(value: 'edit', child: const Text('Modifier'), onTap: () => context.push('/school/sessions/${session.id}/edit')),
        PopupMenuItem(value: 'delete', child: const Text('Supprimer', style: TextStyle(color: AppColors.error)), onTap: () => _delete(context)),
      ]),
    ),
  );
}
