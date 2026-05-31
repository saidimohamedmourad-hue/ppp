import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../../providers/auth_provider.dart';
import '../../providers/training_provider.dart';
import '../../widgets/apply_bottom_sheet.dart';
import '../../../core/constants/app_colors.dart';
import '../../../data/models/training/training_model.dart';
import '../../../data/repositories/training_repository.dart';

class TrainingDetailScreen extends ConsumerWidget {
  const TrainingDetailScreen({super.key, required this.sessionId});
  final String sessionId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final sessionAsync = ref.watch(trainingDetailProvider(sessionId));
    return sessionAsync.when(
      data: (s) => _TrainingDetailBody(session: s),
      loading: () => const Scaffold(body: Center(child: CircularProgressIndicator())),
      error: (e, _) => Scaffold(body: Center(child: Text(e.toString()))),
    );
  }
}

class _TrainingDetailBody extends ConsumerStatefulWidget {
  const _TrainingDetailBody({required this.session});
  final TrainingSessionModel session;

  @override
  ConsumerState<_TrainingDetailBody> createState() => _TrainingDetailBodyState();
}

class _TrainingDetailBodyState extends ConsumerState<_TrainingDetailBody> {
  bool _applying = false;

  Future<void> _apply() async {
    final needsPhone = !(ref.read(authProvider).valueOrNull?.hasPhone ?? true);
    final selection = await showApplyBottomSheet(context, needsPhone: needsPhone);
    if (selection == null || !selection.isValid) return;

    setState(() => _applying = true);
    try {
      await TrainingRepository().applySession(
        widget.session.id,
        resumeId: selection.resumeId,
        filePath: selection.filePath,
        fileName: selection.fileName,
        fileBytes: selection.fileBytes,
        phone: selection.phone,
      );
      if (selection.phone != null && selection.phone!.isNotEmpty) {
        await ref.read(authProvider.notifier).refreshUser();
      }
      ref.invalidate(myTrainingApplicationsProvider);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Text('Inscription envoyée ! L\'analyse IA est en cours (consultez « Mes candidatures »).'),
            backgroundColor: AppColors.success,
            action: SnackBarAction(
              label: 'Voir',
              textColor: Colors.white,
              onPressed: () => context.push('/my-applications'),
            ),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppColors.error),
        );
      }
    } finally {
      if (mounted) setState(() => _applying = false);
    }
  }

  Color _typeColor(TrainingType t) {
    switch (t) {
      case TrainingType.enLigne: return AppColors.blue;
      case TrainingType.accelerer: return AppColors.gold;
      case TrainingType.presentiel: return AppColors.mint;
    }
  }

  @override
  Widget build(BuildContext context) {
    final fmt = DateFormat('dd MMM yyyy', 'fr');
    final s = widget.session;
    final cancelled = s.isCancelled;
    final full = s.isFull && !cancelled;
    final typeColor = _typeColor(s.type);

    return Stack(
      children: [
        CustomScrollView(
          slivers: [
            SliverAppBar(
              expandedHeight: 180,
              pinned: true,
              flexibleSpace: FlexibleSpaceBar(
                background: Container(
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [typeColor.withValues(alpha: 0.6), typeColor.withValues(alpha: 0.2)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                  ),
                  child: const Center(child: Icon(Icons.school, size: 64, color: Colors.white54)),
                ),
              ),
            ),
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (cancelled) _DetailBanner(
                      color: AppColors.error,
                      icon: Icons.block,
                      title: 'Formation annulée',
                      message: s.cancellationReason != null && s.cancellationReason!.isNotEmpty
                          ? 'Raison : ${s.cancellationReason}'
                          : null,
                    ),
                    if (full) _DetailBanner(
                      color: AppColors.gold,
                      icon: Icons.hourglass_bottom,
                      title: 'Formation complète',
                      message: "Tu peux quand même t'inscrire sur la liste d'attente. L'école te contactera si une place se libère.",
                    ),
                    if (cancelled || full) const SizedBox(height: 16),
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Expanded(child: Text(s.title, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold))),
                        const SizedBox(width: 10),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: typeColor.withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(100),
                          ),
                          child: Text(s.type.label, style: TextStyle(fontSize: 11, color: typeColor, fontWeight: FontWeight.w700)),
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    Text(s.school?.name ?? '', style: const TextStyle(fontSize: 15, color: AppColors.muted)),
                    const SizedBox(height: 16),
                    _InfoRow(icon: Icons.location_on_outlined, label: s.location),
                    _InfoRow(icon: Icons.calendar_today_outlined, label: 'Début: ${fmt.format(s.trainingDate)}'),
                    if (s.endDate != null) _InfoRow(icon: Icons.event_outlined, label: 'Fin: ${fmt.format(s.endDate!)}'),
                    _InfoRow(icon: Icons.people_outline, label: '${s.currentParticipants}/${s.maxParticipants} participants'),
                    if (s.salary != null && s.salary! > 0) _InfoRow(icon: Icons.attach_money, label: '${s.salary!.toStringAsFixed(0)} DA'),
                    if (s.trainingCategory != null) _InfoRow(icon: Icons.category_outlined, label: s.trainingCategory!.name),
                    const SizedBox(height: 24),
                    const Text('Description', style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    Text(s.description, style: const TextStyle(height: 1.6)),
                    const SizedBox(height: 100),
                  ],
                ),
              ),
            ),
          ],
        ),
        Positioned(
          bottom: 0, left: 0, right: 0,
          child: Container(
            padding: const EdgeInsets.all(20),
            decoration: const BoxDecoration(
              color: AppColors.surface,
              border: Border(top: BorderSide(color: AppColors.border, width: 1)),
            ),
            child: ElevatedButton(
              onPressed: (cancelled || _applying) ? null : _apply,
              style: cancelled
                  ? ElevatedButton.styleFrom(backgroundColor: AppColors.surface2, foregroundColor: AppColors.muted)
                  : full
                      ? ElevatedButton.styleFrom(backgroundColor: AppColors.gold, foregroundColor: AppColors.bg)
                      : null,
              child: _applying
                  ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: AppColors.bg, strokeWidth: 2))
                  : Text(
                      cancelled
                          ? 'Inscription fermée'
                          : full
                              ? "Rejoindre la liste d'attente"
                              : "S'inscrire",
                    ),
            ),
          ),
        ),
      ],
    );
  }
}

class _DetailBanner extends StatelessWidget {
  const _DetailBanner({required this.color, required this.icon, required this.title, this.message});
  final Color color;
  final IconData icon;
  final String title;
  final String? message;

  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.all(14),
    decoration: BoxDecoration(
      color: color.withValues(alpha: 0.08),
      border: Border.all(color: color.withValues(alpha: 0.3)),
      borderRadius: BorderRadius.circular(14),
    ),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Icon(icon, size: 20, color: color),
            const SizedBox(width: 10),
            Text(title, style: TextStyle(fontWeight: FontWeight.w700, color: color, fontSize: 14)),
          ],
        ),
        if (message != null) ...[
          const SizedBox(height: 8),
          Text(message!, style: const TextStyle(color: AppColors.text, fontSize: 13, height: 1.5)),
        ],
      ],
    ),
  );
}

class _InfoRow extends StatelessWidget {
  const _InfoRow({required this.icon, required this.label});
  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(bottom: 10),
    child: Row(
      children: [
        Icon(icon, size: 18, color: AppColors.muted),
        const SizedBox(width: 10),
        Expanded(child: Text(label, style: const TextStyle(fontSize: 14))),
      ],
    ),
  );
}
