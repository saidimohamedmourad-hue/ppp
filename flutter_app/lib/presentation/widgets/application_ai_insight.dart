import 'package:flutter/material.dart';

import '../../core/constants/app_colors.dart';

/// True tant que le backend n’a pas encore rempli le retour IA (score 0 et pas de texte).
bool applicationAiPending({required int? score, required String? feedback}) {
  final text = feedback?.trim() ?? '';
  if (text.isNotEmpty) return false;
  return (score ?? 0) == 0;
}

void showAiFeedbackDialog(BuildContext context, {required String title, required String body}) {
  showDialog<void>(
    context: context,
    builder: (ctx) => AlertDialog(
      title: Text(title),
      content: SingleChildScrollView(child: Text(body, style: const TextStyle(height: 1.4))),
      actions: [TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Fermer'))],
    ),
  );
}

/// Ligne compacte : état « en cours », score /100, lien vers le détail du feedback.
class ApplicationAiInsightRow extends StatelessWidget {
  const ApplicationAiInsightRow({
    super.key,
    required this.score,
    required this.feedback,
    this.dialogTitle = 'Avis de l’analyse IA',
  });

  final int? score;
  final String? feedback;
  final String dialogTitle;

  @override
  Widget build(BuildContext context) {
    if (applicationAiPending(score: score, feedback: feedback)) {
      return Padding(
        padding: const EdgeInsets.only(top: 6),
        child: Row(
          children: [
            SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.primary)),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                'Analyse du CV en cours (OpenAI)…',
                style: TextStyle(fontSize: 12, color: Colors.grey[700], fontStyle: FontStyle.italic),
              ),
            ),
          ],
        ),
      );
    }

    final text = feedback?.trim() ?? '';
    final displayScore = score ?? 0;

    return Padding(
      padding: const EdgeInsets.only(top: 6),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Score IA : $displayScore / 100',
            style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.w600, fontSize: 13),
          ),
          if (text.isNotEmpty)
            TextButton(
              style: TextButton.styleFrom(
                padding: EdgeInsets.zero,
                minimumSize: Size.zero,
                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
              ),
              onPressed: () => showAiFeedbackDialog(context, title: dialogTitle, body: text),
              child: const Text('Lire l’analyse détaillée', style: TextStyle(fontSize: 13)),
            ),
        ],
      ),
    );
  }
}
