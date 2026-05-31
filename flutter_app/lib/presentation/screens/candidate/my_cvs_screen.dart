import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../providers/resume_provider.dart';
import '../../../core/constants/app_colors.dart';
import '../../../data/repositories/resume_repository.dart';

class MyCvsScreen extends ConsumerStatefulWidget {
  const MyCvsScreen({super.key});

  @override
  ConsumerState<MyCvsScreen> createState() => _MyCvsScreenState();
}

class _MyCvsScreenState extends ConsumerState<MyCvsScreen> {
  bool _uploading = false;

  Future<void> _pickAndUpload() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: ['pdf'],
      withData: true, // requis pour récupérer les bytes sur web
    );
    if (result == null || result.files.isEmpty) return;

    final file = result.files.single;
    final hasPath = file.path != null && file.path!.isNotEmpty;
    final hasBytes = file.bytes != null && file.bytes!.isNotEmpty;
    if (!hasPath && !hasBytes) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Impossible de lire le fichier sélectionné'), backgroundColor: AppColors.error),
        );
      }
      return;
    }

    setState(() => _uploading = true);
    try {
      await ResumeRepository().uploadResume(
        file.name,
        filePath: hasPath ? file.path : null,
        fileBytes: hasBytes ? file.bytes : null,
      );
      ref.invalidate(myResumesProvider);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('CV ajouté avec succès'), backgroundColor: AppColors.success),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppColors.error),
        );
      }
    } finally {
      if (mounted) setState(() => _uploading = false);
    }
  }

  Future<void> _delete(String id, String filename) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Supprimer le CV ?'),
        content: Text('Supprimer "$filename" ?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Annuler')),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Supprimer', style: TextStyle(color: AppColors.error)),
          ),
        ],
      ),
    );
    if (ok == true) {
      try {
        await ResumeRepository().deleteResume(id);
        ref.invalidate(myResumesProvider);
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(e.toString()), backgroundColor: AppColors.error),
          );
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final resumesAsync = ref.watch(myResumesProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Mes CVs'),
        actions: [
          if (_uploading)
            const Padding(
              padding: EdgeInsets.all(14),
              child: SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)),
            )
          else
            IconButton(
              icon: const Icon(Icons.upload_file_outlined),
              tooltip: 'Ajouter un CV (PDF)',
              onPressed: _pickAndUpload,
            ),
        ],
      ),
      body: resumesAsync.when(
        data: (resumes) => resumes.isEmpty
            ? Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.description_outlined, size: 64, color: AppColors.grey),
                    const SizedBox(height: 12),
                    const Text('Aucun CV', style: TextStyle(fontSize: 18, color: AppColors.grey)),
                    const SizedBox(height: 8),
                    const Text('Ajoutez un CV PDF pour postuler plus facilement', textAlign: TextAlign.center, style: TextStyle(color: AppColors.grey)),
                    const SizedBox(height: 24),
                    ElevatedButton.icon(
                      onPressed: _pickAndUpload,
                      icon: const Icon(Icons.upload_file_outlined),
                      label: const Text('Ajouter un CV'),
                    ),
                  ],
                ),
              )
            : ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: resumes.length,
                itemBuilder: (_, i) {
                  final cv = resumes[i];
                  return Card(
                    margin: const EdgeInsets.only(bottom: 10),
                    child: ListTile(
                      leading: const CircleAvatar(
                        backgroundColor: AppColors.primary,
                        child: Icon(Icons.picture_as_pdf, color: Colors.white, size: 20),
                      ),
                      title: Text(cv.filename, style: const TextStyle(fontWeight: FontWeight.w600)),
                      subtitle: Text(
                        'Ajouté le ${DateFormat('dd/MM/yyyy').format(cv.createdAt)}',
                        style: const TextStyle(fontSize: 12, color: AppColors.grey),
                      ),
                      trailing: IconButton(
                        icon: const Icon(Icons.delete_outline, color: AppColors.error),
                        onPressed: () => _delete(cv.id, cv.filename),
                      ),
                    ),
                  );
                },
              ),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text(e.toString())),
      ),
      floatingActionButton: resumesAsync.maybeWhen(
        data: (resumes) => resumes.isNotEmpty
            ? FloatingActionButton.extended(
                onPressed: _uploading ? null : _pickAndUpload,
                icon: const Icon(Icons.upload_file_outlined),
                label: const Text('Ajouter un CV'),
              )
            : null,
        orElse: () => null,
      ),
    );
  }
}
