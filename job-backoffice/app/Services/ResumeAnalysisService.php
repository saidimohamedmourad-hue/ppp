<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;
use Smalot\PdfParser\Parser;

class ResumeAnalysisService
{
    // ─── Extract raw text from a PDF stored locally (public disk) ─────────────

    public function extractRawText(string $fileUri): string
    {
        // fileUri is the relative path stored in DB e.g. "resumes/filename.pdf"
        $fullPath = Storage::disk('public')->path($fileUri);

        if (! file_exists($fullPath)) {
            throw new \Exception("Fichier PDF introuvable : {$fullPath}");
        }

        $parser = new Parser();
        $pdf    = $parser->parseFile($fullPath);
        $text   = $pdf->getText();

        if (empty(trim($text))) {
            throw new \Exception('Le fichier PDF ne contient pas de texte extractible.');
        }

        return $text;
    }

    // ─── Extract structured info from resume (summary, skills, exp, education) ─

    public function extractResumeInformation(string $fileUri): array
    {
        try {
            $rawText = $this->extractRawText($fileUri);

            Log::debug('Texte extrait du CV : ' . strlen($rawText) . ' caractères');

            $response = OpenAI::chat()->create([
                'model'    => 'gpt-4o',
                'messages' => [
                    [
                        'role'    => 'system',
                        'content' => 'Tu es un parseur de CV précis. Extrais les informations exactement telles qu\'elles apparaissent dans le CV, sans interprétation ni information supplémentaire. Le résultat doit être en JSON.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => "Parse ce CV et extrais les informations dans un objet JSON avec les clés exactes : 'summary', 'skills', 'experience', 'education'. Texte du CV : {$rawText}. Retourne une chaîne vide pour les clés non trouvées.",
                    ],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature'     => 0,
            ]);

            $result       = $response->choices[0]->message->content;
            $parsedResult = json_decode($result, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Réponse OpenAI invalide : ' . json_last_error_msg());
            }

            return [
                'summary'    => $parsedResult['summary']    ?? '',
                'skills'     => $parsedResult['skills']     ?? '',
                'experience' => $parsedResult['experience'] ?? '',
                'education'  => $parsedResult['education']  ?? '',
            ];

        } catch (\Exception $e) {
            Log::error('Erreur extraction CV : ' . $e->getMessage());
            return ['summary' => '', 'skills' => '', 'experience' => '', 'education' => ''];
        }
    }

    // ─── Analyse CV vs Offre d'emploi ─────────────────────────────────────────

    public function analyzeResume(array $resumeData, $jobVacancy): array
    {
        try {
            $jobDetails = json_encode([
                'title'       => $jobVacancy->title,
                'description' => $jobVacancy->description,
                'location'    => $jobVacancy->location,
                'type'        => $jobVacancy->type,
                'category'    => optional($jobVacancy->jobCategory)->name,
            ]);

            $resumeDetails = json_encode($resumeData);

            $response = OpenAI::chat()->create([
                'model'    => 'gpt-4o',
                'messages' => [
                    [
                        'role'    => 'system',
                        'content' => "Tu es un expert RH et recruteur. On te donne une offre d'emploi et un CV.
Ta mission : analyser si le candidat est un bon profil pour ce poste.
Critères (sur 100 points) :
- Compétences techniques (40 pts)
- Expérience professionnelle pertinente (30 pts)
- Formation et diplômes (20 pts)
- Autres éléments (langues, projets, etc.) (10 pts)
Réponds UNIQUEMENT en JSON avec les clés : 'aiGeneratedScore' (entier 0-100), 'aiGeneratedFeedback' (texte détaillé en français).",
                    ],
                    [
                        'role'    => 'user',
                        'content' => "Évalue cette candidature.\nOffre d'emploi : {$jobDetails}\nCV : {$resumeDetails}",
                    ],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature'     => 0,
            ]);

            $result       = $response->choices[0]->message->content;
            $parsedResult = json_decode($result, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Réponse OpenAI invalide : ' . json_last_error_msg());
            }

            if (! isset($parsedResult['aiGeneratedScore'], $parsedResult['aiGeneratedFeedback'])) {
                throw new \Exception('Clés manquantes dans la réponse OpenAI.');
            }

            return [
                'aiGeneratedScore'    => (int) $parsedResult['aiGeneratedScore'],
                'aiGeneratedFeedback' => $parsedResult['aiGeneratedFeedback'],
            ];

        } catch (\Exception $e) {
            Log::error('Erreur analyse candidature emploi : ' . $e->getMessage());
            return [
                'aiGeneratedScore'    => 0,
                'aiGeneratedFeedback' => 'Une erreur est survenue lors de l\'analyse IA.',
            ];
        }
    }

    // ─── Analyse CV vs Formation ───────────────────────────────────────────────

    public function analyzeResumeForTraining(array $resumeData, $trainingSession): array
    {
        try {
            $trainingDetails = json_encode([
                'title'       => $trainingSession->title,
                'description' => $trainingSession->description,
                'category'    => optional($trainingSession->trainingCategory)->name,
                'school'      => optional($trainingSession->school)->name,
                'location'    => $trainingSession->location,
            ]);

            $resumeDetails = json_encode($resumeData);

            $response = OpenAI::chat()->create([
                'model'    => 'gpt-4o',
                'messages' => [
                    [
                        'role'    => 'system',
                        'content' => "Tu es un expert en admission de formations. On te donne une formation et un CV de candidat.
Ta mission : évaluer si le candidat est un bon profil pour cette formation.
Critères (sur 100 points) :
- Niveau de formation adéquat (30 pts)
- Expérience et compétences en rapport (35 pts)
- Motivation et cohérence du parcours (25 pts)
- Autres éléments (10 pts)
Réponds UNIQUEMENT en JSON avec les clés : 'aiGeneratedScore' (entier 0-100), 'aiGeneratedFeedback' (texte détaillé en français).",
                    ],
                    [
                        'role'    => 'user',
                        'content' => "Évalue cette candidature à une formation.\nFormation : {$trainingDetails}\nCV : {$resumeDetails}",
                    ],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature'     => 0,
            ]);

            $result       = $response->choices[0]->message->content;
            $parsedResult = json_decode($result, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Réponse OpenAI invalide : ' . json_last_error_msg());
            }

            if (! isset($parsedResult['aiGeneratedScore'], $parsedResult['aiGeneratedFeedback'])) {
                throw new \Exception('Clés manquantes dans la réponse OpenAI.');
            }

            return [
                'aiGeneratedScore'    => (int) $parsedResult['aiGeneratedScore'],
                'aiGeneratedFeedback' => $parsedResult['aiGeneratedFeedback'],
            ];

        } catch (\Exception $e) {
            Log::error('Erreur analyse candidature formation : ' . $e->getMessage());
            return [
                'aiGeneratedScore'    => 0,
                'aiGeneratedFeedback' => 'Une erreur est survenue lors de l\'analyse IA.',
            ];
        }
    }
}
