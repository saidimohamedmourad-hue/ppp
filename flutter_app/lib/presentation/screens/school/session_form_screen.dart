import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../providers/training_provider.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/education_levels.dart';
import '../../../data/models/training/training_model.dart';
import '../../../data/repositories/training_repository.dart';

class SessionFormScreen extends ConsumerStatefulWidget {
  const SessionFormScreen({super.key, this.sessionId});
  final String? sessionId;

  @override
  ConsumerState<SessionFormScreen> createState() => _SessionFormScreenState();
}

class _SessionFormScreenState extends ConsumerState<SessionFormScreen> {
  final _form = GlobalKey<FormState>();
  final _title = TextEditingController();
  final _description = TextEditingController();
  final _location = TextEditingController();
  final _maxParticipants = TextEditingController();
  final _cancellationReason = TextEditingController();
  String _status = 'open';
  TrainingType _type = TrainingType.presentiel;
  String? _minEducationLevel;
  String? _trainingCategoryId;
  DateTime? _startDate;
  bool _loading = false;
  bool _loadingData = false;
  bool get _isEdit => widget.sessionId != null;

  @override
  void initState() {
    super.initState();
    if (_isEdit) _loadExistingSession();
  }

  Future<void> _loadExistingSession() async {
    setState(() => _loadingData = true);
    try {
      final session = await TrainingRepository().getSession(widget.sessionId!);
      if (mounted) {
        setState(() {
          _title.text = session.title;
          _description.text = session.description;
          _location.text = session.location;
          _maxParticipants.text = session.maxParticipants.toString();
          _status = session.status;
          _type = session.type;
          _minEducationLevel = session.minEducationLevel;
          _cancellationReason.text = session.cancellationReason ?? '';
          _startDate = session.trainingDate;
          _trainingCategoryId = session.trainingCategory?.id;
        });
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString()), backgroundColor: AppColors.error));
    } finally {
      if (mounted) setState(() => _loadingData = false);
    }
  }

  @override
  void dispose() {
    _title.dispose(); _description.dispose(); _location.dispose(); _maxParticipants.dispose();
    _cancellationReason.dispose();
    super.dispose();
  }

  Future<void> _pickDate() async {
    final d = await showDatePicker(
      context: context,
      initialDate: _startDate ?? DateTime.now(),
      firstDate: DateTime(2020),
      lastDate: DateTime.now().add(const Duration(days: 365 * 2)),
    );
    if (d != null) setState(() => _startDate = d);
  }

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    if (_startDate == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Choisissez une date')));
      return;
    }
    setState(() => _loading = true);
    try {
      final data = {
        'title': _title.text.trim(),
        'description': _description.text.trim(),
        'location': _location.text.trim(),
        'maxParticipants': int.parse(_maxParticipants.text),
        'trainingDate': _startDate!.toIso8601String().substring(0, 10),
        'status': _status,
        'type': _type.value,
        'min_education_level': _minEducationLevel,
        if (_status == 'cancelled') 'cancellation_reason': _cancellationReason.text.trim(),
        'trainingCategoryId': _trainingCategoryId,
      };
      if (_isEdit) {
        await TrainingRepository().updateSession(widget.sessionId!, data);
      } else {
        await TrainingRepository().createSession(data);
      }
      ref.invalidate(schoolSessionsProvider);
      if (mounted) context.pop();
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString()), backgroundColor: AppColors.error));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loadingData) {
      return Scaffold(
        appBar: AppBar(title: const Text('Chargement...')),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    return Scaffold(
      appBar: AppBar(title: Text(_isEdit ? 'Modifier la formation' : 'Nouvelle formation')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _form,
          child: Column(
            children: [
              TextFormField(controller: _title, decoration: const InputDecoration(labelText: 'Titre'), validator: (v) => v?.isEmpty == true ? 'Requis' : null),
              const SizedBox(height: 16),
              TextFormField(controller: _description, decoration: const InputDecoration(labelText: 'Description'), maxLines: 4, validator: (v) => v?.isEmpty == true ? 'Requis' : null),
              const SizedBox(height: 16),
              TextFormField(controller: _location, decoration: const InputDecoration(labelText: 'Lieu', prefixIcon: Icon(Icons.location_on_outlined)), validator: (v) => v?.isEmpty == true ? 'Requis' : null),
              const SizedBox(height: 16),
              TextFormField(
                controller: _maxParticipants,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(labelText: 'Nombre maximum de participants', prefixIcon: Icon(Icons.people_outline)),
                validator: (v) => (v == null || v.isEmpty || int.tryParse(v) == null) ? 'Nombre invalide' : null,
              ),
              const SizedBox(height: 16),
              ListTile(
                contentPadding: EdgeInsets.zero,
                leading: const Icon(Icons.calendar_today_outlined, color: AppColors.primary),
                title: Text(_startDate == null ? 'Date de début *' : '${_startDate!.day}/${_startDate!.month}/${_startDate!.year}'),
                trailing: const Icon(Icons.chevron_right),
                onTap: _pickDate,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12), side: const BorderSide(color: AppColors.lightGrey)),
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<String>(
                initialValue: _status,
                decoration: const InputDecoration(labelText: 'Statut'),
                items: const [
                  DropdownMenuItem(value: 'open', child: Text('Ouverte')),
                  DropdownMenuItem(value: 'closed', child: Text('Fermée (cachée)')),
                  DropdownMenuItem(value: 'cancelled', child: Text('Annulée')),
                ],
                onChanged: (v) => setState(() => _status = v!),
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<TrainingType>(
                initialValue: _type,
                decoration: const InputDecoration(labelText: 'Type'),
                items: TrainingType.values
                    .map((t) => DropdownMenuItem(value: t, child: Text(t.label)))
                    .toList(),
                onChanged: (v) => setState(() => _type = v!),
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<String>(
                initialValue: _minEducationLevel,
                isExpanded: true,
                decoration: const InputDecoration(
                  labelText: 'Niveau d\'études minimum requis *',
                  prefixIcon: Icon(Icons.school_outlined),
                ),
                items: kEducationLevels
                    .map((lvl) => DropdownMenuItem(value: lvl, child: Text(lvl)))
                    .toList(),
                onChanged: (v) => setState(() => _minEducationLevel = v),
                validator: (v) => (v == null || v.isEmpty) ? 'Sélectionnez un niveau' : null,
              ),
              const SizedBox(height: 16),
              if (_status == 'cancelled') ...[
                TextFormField(
                  controller: _cancellationReason,
                  maxLines: 3,
                  decoration: const InputDecoration(
                    labelText: "Raison de l'annulation *",
                    hintText: 'Ex: effectif insuffisant, formateur indisponible...',
                  ),
                  validator: (v) => (_status == 'cancelled' && (v?.trim().isEmpty ?? true)) ? 'Requis' : null,
                ),
                const SizedBox(height: 16),
              ],
              ref.watch(trainingCategoriesProvider).when(
                data: (categories) => DropdownButtonFormField<String>(
                  initialValue: _trainingCategoryId,
                  decoration: const InputDecoration(labelText: 'Catégorie de formation'),
                  items: categories.map((c) => DropdownMenuItem(value: c.id, child: Text(c.name))).toList(),
                  onChanged: (v) => setState(() => _trainingCategoryId = v),
                  validator: (v) => v == null ? 'Choisissez une catégorie' : null,
                ),
                loading: () => const LinearProgressIndicator(),
                error: (e, _) => Text('Impossible de charger les catégories: $e', style: const TextStyle(color: AppColors.error)),
              ),
              const SizedBox(height: 32),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _loading ? null : _submit,
                  child: _loading
                      ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                      : Text(_isEdit ? 'Enregistrer' : 'Créer la formation'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
