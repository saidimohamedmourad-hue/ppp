import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../providers/training_provider.dart';
import '../../../core/constants/app_colors.dart';
import '../../../data/models/training/training_model.dart';

class TrainingListScreen extends ConsumerStatefulWidget {
  const TrainingListScreen({super.key});

  @override
  ConsumerState<TrainingListScreen> createState() => _TrainingListScreenState();
}

class _TrainingListScreenState extends ConsumerState<TrainingListScreen> {
  final _search = TextEditingController();
  TrainingType? _typeFilter;

  Color _colorFor(TrainingType t) {
    switch (t) {
      case TrainingType.enLigne:
        return AppColors.blue;
      case TrainingType.accelerer:
        return AppColors.gold;
      case TrainingType.presentiel:
        return AppColors.mint;
    }
  }

  @override
  Widget build(BuildContext context) {
    final sessionsAsync = ref.watch(trainingListProvider((
      search: _search.text.isEmpty ? null : _search.text,
      category: null,
      type: _typeFilter?.value,
    )));

    return Scaffold(
      appBar: AppBar(title: const Text('Formations')),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
            child: TextField(
              controller: _search,
              decoration: InputDecoration(
                hintText: 'Rechercher une formation...',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: _search.text.isNotEmpty
                    ? IconButton(icon: const Icon(Icons.clear), onPressed: () { _search.clear(); setState(() {}); })
                    : null,
              ),
              onChanged: (_) => setState(() {}),
            ),
          ),
          SizedBox(
            height: 44,
            child: ListView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 16),
              children: [
                _TypeChip(label: 'Tous', selected: _typeFilter == null, color: AppColors.mint,
                  onTap: () => setState(() => _typeFilter = null)),
                ...TrainingType.values.map((t) => _TypeChip(
                  label: t.label, selected: _typeFilter == t, color: _colorFor(t),
                  onTap: () => setState(() => _typeFilter = t),
                )),
              ],
            ),
          ),
          const SizedBox(height: 6),
          Expanded(
            child: sessionsAsync.when(
              data: (sessions) => sessions.isEmpty
                  ? const Center(child: Text('Aucune formation disponible', style: TextStyle(color: AppColors.muted)))
                  : ListView.builder(
                      padding: const EdgeInsets.fromLTRB(16, 4, 16, 16),
                      itemCount: sessions.length,
                      itemBuilder: (_, i) => _SessionTile(session: sessions[i], typeColor: _colorFor(sessions[i].type)),
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

class _TypeChip extends StatelessWidget {
  const _TypeChip({required this.label, required this.selected, required this.color, required this.onTap});
  final String label;
  final bool selected;
  final Color color;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(right: 8),
    child: InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(100),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: selected ? color.withValues(alpha: 0.12) : AppColors.surface2,
          border: Border.all(color: selected ? color.withValues(alpha: 0.4) : AppColors.border),
          borderRadius: BorderRadius.circular(100),
        ),
        child: Text(label, style: TextStyle(
          fontSize: 13,
          fontWeight: selected ? FontWeight.w700 : FontWeight.w500,
          color: selected ? color : AppColors.muted,
        )),
      ),
    ),
  );
}

class _SessionTile extends StatelessWidget {
  const _SessionTile({required this.session, required this.typeColor});
  final TrainingSessionModel session;
  final Color typeColor;

  @override
  Widget build(BuildContext context) {
    final cancelled = session.isCancelled;
    return Opacity(
      opacity: cancelled ? 0.7 : 1,
      child: Card(
        margin: const EdgeInsets.only(bottom: 10),
        child: InkWell(
          onTap: () => context.push('/training/${session.id}'),
          borderRadius: BorderRadius.circular(16),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(child: Text(session.title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15))),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: typeColor.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(100),
                      ),
                      child: Text(session.type.label,
                        style: TextStyle(fontSize: 11, color: typeColor, fontWeight: FontWeight.w700),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                Text(session.school?.name ?? '', style: const TextStyle(color: AppColors.muted, fontSize: 13)),
                const SizedBox(height: 8),
                Row(
                  children: [
                    const Icon(Icons.location_on_outlined, size: 13, color: AppColors.muted),
                    const SizedBox(width: 2),
                    Expanded(child: Text(session.location, style: const TextStyle(color: AppColors.muted, fontSize: 12), overflow: TextOverflow.ellipsis)),
                    const Icon(Icons.people_outline, size: 13, color: AppColors.muted),
                    const SizedBox(width: 2),
                    Text('${session.currentParticipants}/${session.maxParticipants}', style: const TextStyle(color: AppColors.muted, fontSize: 12)),
                  ],
                ),
                if (cancelled || session.isFull) ...[
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      if (cancelled)
                        _StatusPill(text: 'Annulée', color: AppColors.error, icon: Icons.block)
                      else if (session.isFull)
                        _StatusPill(text: "Complète — liste d'attente", color: AppColors.gold, icon: Icons.hourglass_bottom),
                    ],
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _StatusPill extends StatelessWidget {
  const _StatusPill({required this.text, required this.color, required this.icon});
  final String text;
  final Color color;
  final IconData icon;

  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
    decoration: BoxDecoration(
      color: color.withValues(alpha: 0.12),
      border: Border.all(color: color.withValues(alpha: 0.25)),
      borderRadius: BorderRadius.circular(100),
    ),
    child: Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 12, color: color),
        const SizedBox(width: 5),
        Text(text, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: color)),
      ],
    ),
  );
}
