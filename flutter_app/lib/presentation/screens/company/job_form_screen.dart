import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../providers/job_provider.dart';
import '../../../core/constants/app_colors.dart';
import '../../../data/repositories/job_repository.dart';

class JobFormScreen extends ConsumerStatefulWidget {
  const JobFormScreen({super.key, this.jobId});
  final String? jobId;

  @override
  ConsumerState<JobFormScreen> createState() => _JobFormScreenState();
}

class _JobFormScreenState extends ConsumerState<JobFormScreen> {
  final _form = GlobalKey<FormState>();
  final _title = TextEditingController();
  final _description = TextEditingController();
  final _location = TextEditingController();
  final _salary = TextEditingController();
  String _type = 'Full-time';
  String? _jobCategoryId;
  bool _loading = false;
  bool _loadingData = false;
  bool get _isEdit => widget.jobId != null;

  @override
  void initState() {
    super.initState();
    if (_isEdit) _loadExistingJob();
  }

  Future<void> _loadExistingJob() async {
    setState(() => _loadingData = true);
    try {
      final job = await JobRepository().getJob(widget.jobId!);
      if (mounted) {
        setState(() {
          _title.text = job.title;
          _description.text = job.description;
          _location.text = job.location;
          _salary.text = job.salary.toStringAsFixed(0);
          _type = job.type;
          _jobCategoryId = job.jobCategory?.id;
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
    _title.dispose(); _description.dispose(); _location.dispose(); _salary.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    setState(() => _loading = true);
    try {
      final data = {
        'title': _title.text.trim(),
        'description': _description.text.trim(),
        'location': _location.text.trim(),
        'salary': double.parse(_salary.text),
        'type': _type,
        'jobCategoryId': _jobCategoryId,
      };
      if (_isEdit) {
        await JobRepository().updateJob(widget.jobId!, data);
      } else {
        await JobRepository().createJob(data);
      }
      ref.invalidate(companyJobsProvider);
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
      appBar: AppBar(title: Text(_isEdit ? 'Modifier l\'offre' : 'Nouvelle offre')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _form,
          child: Column(
            children: [
              TextFormField(
                controller: _title,
                decoration: const InputDecoration(labelText: 'Titre du poste'),
                validator: (v) => v?.isEmpty == true ? 'Requis' : null,
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _description,
                decoration: const InputDecoration(labelText: 'Description'),
                maxLines: 5,
                validator: (v) => v?.isEmpty == true ? 'Requis' : null,
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _location,
                decoration: const InputDecoration(labelText: 'Lieu', prefixIcon: Icon(Icons.location_on_outlined)),
                validator: (v) => v?.isEmpty == true ? 'Requis' : null,
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _salary,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(labelText: 'Salaire annuel (DA)', prefixIcon: Icon(Icons.attach_money)),
                validator: (v) => (v == null || v.isEmpty || double.tryParse(v) == null) ? 'Montant invalide' : null,
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<String>(
                initialValue: _type,
                decoration: const InputDecoration(labelText: 'Type de contrat'),
                items: const [
                  DropdownMenuItem(value: 'Full-time', child: Text('Temps plein')),
                  DropdownMenuItem(value: 'Contract', child: Text('Contrat')),
                  DropdownMenuItem(value: 'Remote', child: Text('Télétravail')),
                  DropdownMenuItem(value: 'Hybrid', child: Text('Hybride')),
                ],
                onChanged: (v) => setState(() => _type = v!),
              ),
              const SizedBox(height: 16),
              ref.watch(jobCategoriesProvider).when(
                data: (categories) => DropdownButtonFormField<String>(
                  initialValue: _jobCategoryId,
                  decoration: const InputDecoration(labelText: 'Catégorie'),
                  items: categories.map((c) => DropdownMenuItem(value: c.id, child: Text(c.name))).toList(),
                  onChanged: (v) => setState(() => _jobCategoryId = v),
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
                      : Text(_isEdit ? 'Enregistrer' : 'Publier l\'offre'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
