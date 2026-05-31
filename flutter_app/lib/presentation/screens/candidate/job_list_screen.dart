import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../providers/job_provider.dart';
import '../../../core/constants/app_colors.dart';
import '../../../data/models/job/job_model.dart';

class JobListScreen extends ConsumerStatefulWidget {
  const JobListScreen({super.key});

  @override
  ConsumerState<JobListScreen> createState() => _JobListScreenState();
}

class _JobListScreenState extends ConsumerState<JobListScreen> {
  final _search = TextEditingController();
  String? _selectedType;
  final _types = ['Full-time', 'Contract', 'Remote', 'Hybrid'];

  @override
  Widget build(BuildContext context) {
    final jobsAsync = ref.watch(jobListProvider((
      search: _search.text.isEmpty ? null : _search.text,
      type: _selectedType,
      category: null,
    )));

    return Scaffold(
      appBar: AppBar(title: const Text('Offres d\'emploi')),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                TextField(
                  controller: _search,
                  decoration: InputDecoration(
                    hintText: 'Rechercher un emploi...',
                    prefixIcon: const Icon(Icons.search),
                    suffixIcon: _search.text.isNotEmpty
                        ? IconButton(icon: const Icon(Icons.clear), onPressed: () { _search.clear(); setState(() {}); })
                        : null,
                  ),
                  onChanged: (_) => setState(() {}),
                ),
                const SizedBox(height: 12),
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      _FilterChip(label: 'Tous', selected: _selectedType == null, onTap: () => setState(() => _selectedType = null)),
                      ..._types.map((t) => _FilterChip(label: t, selected: _selectedType == t, onTap: () => setState(() => _selectedType = t))),
                    ],
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: jobsAsync.when(
              data: (jobs) => jobs.isEmpty
                  ? const Center(child: Text('Aucune offre trouvée'))
                  : ListView.builder(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      itemCount: jobs.length,
                      itemBuilder: (_, i) => _JobListTile(job: jobs[i]),
                    ),
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (e, _) => Center(child: Text(e.toString())),
            ),
          ),
        ],
      ),
    );
  }
}

class _FilterChip extends StatelessWidget {
  const _FilterChip({required this.label, required this.selected, required this.onTap});
  final String label;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(right: 8),
    child: GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: selected ? AppColors.primary : AppColors.lightGrey,
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(label, style: TextStyle(color: selected ? Colors.white : AppColors.grey, fontWeight: FontWeight.w500, fontSize: 13)),
      ),
    ),
  );
}

class _JobListTile extends StatelessWidget {
  const _JobListTile({required this.job});
  final JobModel job;

  @override
  Widget build(BuildContext context) => Card(
    margin: const EdgeInsets.only(bottom: 10),
    child: InkWell(
      onTap: () => context.push('/jobs/${job.id}'),
      borderRadius: BorderRadius.circular(16),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            CircleAvatar(
              radius: 26,
              backgroundColor: AppColors.primary.withValues(alpha: 0.1),
              child: const Icon(Icons.business, color: AppColors.primary),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(job.title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                  const SizedBox(height: 4),
                  Text(job.company?.name ?? '', style: const TextStyle(color: AppColors.grey, fontSize: 13)),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      const Icon(Icons.location_on_outlined, size: 13, color: AppColors.grey),
                      const SizedBox(width: 2),
                      Text(job.location, style: const TextStyle(color: AppColors.grey, fontSize: 12)),
                      const SizedBox(width: 12),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(color: AppColors.primary.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(8)),
                        child: Text(job.type, style: const TextStyle(color: AppColors.primary, fontSize: 11, fontWeight: FontWeight.w600)),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text('${job.salary.toStringAsFixed(0)} DA', style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.dark)),
                const Text('/an', style: TextStyle(color: AppColors.grey, fontSize: 11)),
              ],
            ),
          ],
        ),
      ),
    ),
  );
}
