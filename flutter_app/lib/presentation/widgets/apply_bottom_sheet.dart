import 'dart:typed_data';

import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/constants/app_colors.dart';
import '../../core/constants/education_levels.dart';
import '../providers/resume_provider.dart';

/// Résultat de la sélection dans le bottom sheet.
class ApplySelection {
  final String? resumeId;
  final String? filePath;
  final String? fileName;
  /// Présent quand [filePath] est indisponible (Android scoped storage, web, etc.).
  final Uint8List? fileBytes;
  /// Numéro de téléphone fraîchement saisi quand l'utilisateur n'en avait
  /// pas encore sur son profil. À transmettre tel quel au backend qui le
  /// persistera sur le profil au moment de la candidature.
  final String? phone;
  /// Niveau d'études (Algérie) — toujours obligatoire.
  final String? educationLevel;

  const ApplySelection({
    this.resumeId,
    this.filePath,
    this.fileName,
    this.fileBytes,
    this.phone,
    this.educationLevel,
  });

  bool get hasCv {
    if (resumeId != null) return true;
    if (fileName == null || fileName!.isEmpty) return false;
    if (filePath != null) return true;
    return fileBytes != null && fileBytes!.isNotEmpty;
  }

  /// Le niveau d'études est obligatoire ; le CV l'est seulement pour les emplois
  /// (cette contrainte est vérifiée en amont par le bottom sheet via cvRequired).
  bool get isValid => educationLevel != null && educationLevel!.isNotEmpty;
}

/// Affiche un bottom sheet pour choisir un CV existant ou uploader un nouveau PDF.
///
/// Si [needsPhone] est vrai (le compte n'a pas de numéro), un champ téléphone
/// supplémentaire apparaît, obligatoire.
/// [cvRequired] vaut true pour une candidature emploi (CV obligatoire) et
/// false pour une inscription formation (CV facultatif).
/// Retourne un [ApplySelection] ou null si annulé.
Future<ApplySelection?> showApplyBottomSheet(
  BuildContext context, {
  bool needsPhone = false,
  bool cvRequired = true,
}) {
  return showModalBottomSheet<ApplySelection>(
    context: context,
    isScrollControlled: true,
    backgroundColor: Colors.transparent,
    builder: (_) => _ApplyBottomSheet(needsPhone: needsPhone, cvRequired: cvRequired),
  );
}

class _ApplyBottomSheet extends ConsumerStatefulWidget {
  const _ApplyBottomSheet({required this.needsPhone, required this.cvRequired});
  final bool needsPhone;
  final bool cvRequired;

  @override
  ConsumerState<_ApplyBottomSheet> createState() => _ApplyBottomSheetState();
}

class _ApplyBottomSheetState extends ConsumerState<_ApplyBottomSheet> {
  String? _selectedResumeId;
  String? _uploadedFilePath;
  Uint8List? _uploadedFileBytes;
  String? _uploadedFileName;
  String? _educationLevel;
  final _phoneCtrl = TextEditingController();

  @override
  void dispose() {
    _phoneCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final resumesAsync = ref.watch(myResumesProvider);
    final bottomInset = MediaQuery.of(context).viewInsets.bottom;
    final maxH = MediaQuery.sizeOf(context).height * 0.92;

    return Container(
      constraints: BoxConstraints(maxHeight: maxH),
      decoration: const BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
        border: Border(top: BorderSide(color: AppColors.border, width: 1)),
      ),
      child: SingleChildScrollView(
        padding: EdgeInsets.fromLTRB(20, 20, 20, bottomInset + 20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Indicateur de glissement
            Center(
              child: Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey[300],
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Niveau d'études (Algérie) — obligatoire.
            const Text(
              'Niveau d\'études *',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            DropdownButtonFormField<String>(
              initialValue: _educationLevel,
              isExpanded: true,
              decoration: const InputDecoration(
                hintText: 'Sélectionnez votre niveau',
                prefixIcon: Icon(Icons.school_outlined),
                border: OutlineInputBorder(),
                contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 4),
              ),
              items: kEducationLevels
                  .map((lvl) => DropdownMenuItem(value: lvl, child: Text(lvl)))
                  .toList(),
              onChanged: (v) => setState(() => _educationLevel = v),
            ),
            const SizedBox(height: 20),

            Text(
              widget.cvRequired ? 'Choisir un CV *' : 'Choisir un CV (optionnel)',
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 4),
            Text(
              widget.cvRequired
                  ? 'Sélectionnez un CV existant ou importez un nouveau fichier PDF.'
                  : 'Facultatif pour une formation. En ajouter un améliore l\'analyse de votre profil.',
              style: TextStyle(fontSize: 13, color: Colors.grey[600]),
            ),
            const SizedBox(height: 16),

            // Liste des CVs existants
            resumesAsync.when(
              loading: () => const Center(child: Padding(
                padding: EdgeInsets.all(16.0),
                child: CircularProgressIndicator(),
              )),
              error: (e, _) => Text('Erreur : $e', style: const TextStyle(color: Colors.red)),
              data: (resumes) {
                if (resumes.isEmpty) {
                  return Padding(
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    child: Text(
                      'Aucun CV enregistré.',
                      style: TextStyle(color: Colors.grey[600]),
                    ),
                  );
                }
                return RadioGroup<String>(
                  groupValue: _selectedResumeId,
                  onChanged: (value) => setState(() {
                    _selectedResumeId = value;
                    _uploadedFilePath = null;
                    _uploadedFileBytes = null;
                    _uploadedFileName = null;
                  }),
                  child: ListView.separated(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: resumes.length,
                    separatorBuilder: (_, __) => const Divider(height: 1),
                    itemBuilder: (context, i) {
                      final resume = resumes[i];
                      return RadioListTile<String>(
                        value: resume.id,
                        title: Text(resume.filename, style: const TextStyle(fontWeight: FontWeight.w500)),
                        subtitle: Text(
                          'Ajouté le ${resume.createdAt.day.toString().padLeft(2, '0')}/${resume.createdAt.month.toString().padLeft(2, '0')}/${resume.createdAt.year}',
                          style: const TextStyle(fontSize: 12),
                        ),
                        contentPadding: EdgeInsets.zero,
                        dense: true,
                        activeColor: Theme.of(context).primaryColor,
                      );
                    },
                  ),
                );
              },
            ),

            const SizedBox(height: 12),
            const Divider(),
            const SizedBox(height: 8),

            // Bouton upload PDF
            InkWell(
              onTap: _pickFile,
              borderRadius: BorderRadius.circular(10),
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
                decoration: BoxDecoration(
                  border: Border.all(
                    color: _uploadedFileName != null
                        ? Theme.of(context).primaryColor
                        : Colors.grey[300]!,
                  ),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Row(
                  children: [
                    Icon(
                      Icons.upload_file,
                      color: _uploadedFileName != null
                          ? Theme.of(context).primaryColor
                          : Colors.grey[600],
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        _uploadedFileName ?? 'Importer un nouveau PDF…',
                        style: TextStyle(
                          color: _uploadedFileName != null
                              ? Theme.of(context).primaryColor
                              : Colors.grey[700],
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    if (_uploadedFileName != null)
                      GestureDetector(
                        onTap: () => setState(() {
                          _uploadedFilePath = null;
                          _uploadedFileBytes = null;
                          _uploadedFileName = null;
                        }),
                        child: const Icon(Icons.close, size: 18, color: Colors.grey),
                      ),
                  ],
                ),
              ),
            ),

            if (widget.needsPhone) ...[
              const SizedBox(height: 16),
              TextField(
                controller: _phoneCtrl,
                keyboardType: TextInputType.phone,
                autofillHints: const [AutofillHints.telephoneNumber],
                onChanged: (_) => setState(() {}), // re-evaluate _canSubmit
                decoration: const InputDecoration(
                  labelText: 'Téléphone *',
                  helperText: 'Le recruteur s\'en servira pour vous contacter.',
                  prefixIcon: Icon(Icons.phone_outlined),
                ),
              ),
            ],
            const SizedBox(height: 20),

            // Bouton postuler
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _canSubmit() ? _submit : null,
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                child: const Text('Postuler', style: TextStyle(fontSize: 16)),
              ),
            ),
            const SizedBox(height: 8),
            SizedBox(
              width: double.infinity,
              child: TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text('Annuler'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _pickFile() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: ['pdf'],
      withData: true,
    );
    if (result == null || result.files.isEmpty) return;
    final f = result.files.single;
    final name = f.name;
    if (name.isEmpty) return;

    final path = f.path;
    final bytes = f.bytes;

    setState(() {
      _selectedResumeId = null;
      _uploadedFileName = name;
      if (path != null && path.isNotEmpty) {
        _uploadedFilePath = path;
        _uploadedFileBytes = null;
      } else if (bytes != null && bytes.isNotEmpty) {
        _uploadedFilePath = null;
        _uploadedFileBytes = bytes;
      } else {
        _uploadedFilePath = null;
        _uploadedFileBytes = null;
        _uploadedFileName = null;
      }
    });
  }

  bool _canSubmit() {
    // Niveau d'études toujours obligatoire.
    if (_educationLevel == null || _educationLevel!.isEmpty) return false;
    final hasCv = _selectedResumeId != null
        || _uploadedFilePath != null
        || (_uploadedFileBytes != null && _uploadedFileBytes!.isNotEmpty);
    // CV obligatoire seulement pour une candidature emploi.
    if (widget.cvRequired && !hasCv) return false;
    if (widget.needsPhone) {
      final t = _phoneCtrl.text.trim();
      if (t.length < 6) return false;
      if (!RegExp(r'^[0-9+\-\s()]+$').hasMatch(t)) return false;
    }
    return true;
  }

  void _submit() {
    Navigator.pop(
      context,
      ApplySelection(
        resumeId: _selectedResumeId,
        filePath: _uploadedFilePath,
        fileName: _uploadedFileName,
        fileBytes: _uploadedFileBytes,
        phone: widget.needsPhone ? _phoneCtrl.text.trim() : null,
        educationLevel: _educationLevel,
      ),
    );
  }
}
