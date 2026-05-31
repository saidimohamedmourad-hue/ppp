import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../providers/auth_provider.dart';
import '../../providers/job_provider.dart';
import '../../widgets/apply_bottom_sheet.dart';
import '../../../core/constants/app_colors.dart';
import '../../../data/models/job/job_model.dart';
import '../../../data/repositories/job_repository.dart';

class JobDetailScreen extends ConsumerWidget {
  const JobDetailScreen({super.key, required this.jobId});
  final String jobId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final jobAsync = ref.watch(jobDetailProvider(jobId));

    return Scaffold(
      body: jobAsync.when(
        data: (job) => _JobDetailBody(job: job),
        loading: () => const Scaffold(body: Center(child: CircularProgressIndicator())),
        error: (e, _) => Scaffold(body: Center(child: Text(e.toString()))),
      ),
    );
  }
}

class _JobDetailBody extends ConsumerStatefulWidget {
  const _JobDetailBody({required this.job});
  final JobModel job;

  @override
  ConsumerState<_JobDetailBody> createState() => _JobDetailBodyState();
}

class _JobDetailBodyState extends ConsumerState<_JobDetailBody> {
  bool _applying = false;

  Future<void> _apply() async {
    final needsPhone = !(ref.read(authProvider).valueOrNull?.hasPhone ?? true);
    final selection = await showApplyBottomSheet(context, needsPhone: needsPhone);
    if (selection == null || !selection.isValid) return;

    setState(() => _applying = true);
    try {
      await JobRepository().applyJob(
        widget.job.id,
        resumeId: selection.resumeId,
        filePath: selection.filePath,
        fileName: selection.fileName,
        fileBytes: selection.fileBytes,
        phone: selection.phone,
      );
      // Refresh the cached user so future applies skip the phone step.
      if (selection.phone != null && selection.phone!.isNotEmpty) {
        await ref.read(authProvider.notifier).refreshUser();
      }
      ref.invalidate(myJobApplicationsProvider);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Text('Candidature envoyée ! L\'analyse IA est en cours (consultez « Mes candidatures »).'),
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

  @override
  Widget build(BuildContext context) => Stack(
    children: [
      CustomScrollView(
        slivers: [
          SliverAppBar(
            expandedHeight: 200,
            pinned: true,
            flexibleSpace: FlexibleSpaceBar(
              background: Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(colors: [AppColors.primary, AppColors.secondary], begin: Alignment.topLeft, end: Alignment.bottomRight),
                ),
                child: const Center(child: Icon(Icons.business, size: 64, color: Colors.white54)),
              ),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(widget.job.title, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  Text(widget.job.company?.name ?? '', style: const TextStyle(fontSize: 16, color: AppColors.grey)),
                  const SizedBox(height: 16),
                  Wrap(
                    spacing: 8, runSpacing: 8,
                    children: [
                      _Tag(icon: Icons.location_on_outlined, label: widget.job.location),
                      _Tag(icon: Icons.work_outline, label: widget.job.type, color: AppColors.primary),
                      _Tag(icon: Icons.attach_money, label: '${widget.job.salary.toStringAsFixed(0)} DA/an', color: AppColors.success),
                      if (widget.job.jobCategory != null)
                        _Tag(icon: Icons.category_outlined, label: widget.job.jobCategory!.name, color: AppColors.secondary),
                    ],
                  ),
                  const SizedBox(height: 24),
                  const Text('Description', style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  Text(widget.job.description, style: const TextStyle(height: 1.6, color: AppColors.dark)),
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
            onPressed: _applying ? null : _apply,
            child: _applying
                ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: AppColors.bg, strokeWidth: 2))
                : const Text('Postuler maintenant'),
          ),
        ),
      ),
    ],
  );
}

class _Tag extends StatelessWidget {
  const _Tag({required this.icon, required this.label, this.color = AppColors.grey});
  final IconData icon;
  final String label;
  final Color color;

  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
    decoration: BoxDecoration(color: color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(8)),
    child: Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 14, color: color),
        const SizedBox(width: 4),
        Text(label, style: TextStyle(fontSize: 12, color: color, fontWeight: FontWeight.w600)),
      ],
    ),
  );
}
