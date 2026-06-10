<?php

/*
 |--------------------------------------------------------------------------
 | Render-test des vues Blade (hors requête HTTP)
 |--------------------------------------------------------------------------
 |
 | Usage :  php artisan tinker scripts/render-test-views.php
 |
 | Pourquoi ce script ?
 | En CLI (tinker), Laravel n'exécute pas le middleware web, donc :
 |   - aucun utilisateur n'est authentifié  -> auth()->user()->role plante
 |   - le ViewErrorBag $errors n'est pas partagé -> $errors->has() plante
 | Ces erreurs sont des artefacts du contexte CLI, PAS des bugs des vues.
 | On reconstitue ici le contexte manquant pour obtenir un rendu fidèle.
 |
*/

use App\Models\Company;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\School;
use App\Models\TrainingApplication;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;

// 1. Authentifier un admin pour que auth()->user()->role fonctionne.
$admin = User::where('role', 'admin')->first();
if ($admin) {
    Auth::login($admin);
    echo "Auth: admin {$admin->email}\n";
} else {
    echo "ATTENTION: aucun admin trouvé, les vérifications de rôle seront ignorées.\n";
}

// 2. Partager un ViewErrorBag vide (comme le fait ShareErrorsFromSession en HTTP).
view()->share('errors', new ViewErrorBag);

$render = function (string $label, string $view, array $data): void {
    try {
        $html = view($view, $data)->render();
        echo sprintf("OK    : %-26s (%d bytes)\n", $label, strlen($html));
    } catch (\Throwable $e) {
        echo sprintf("ERROR : %-26s -> %s\n", $label, $e->getMessage());
    }
};

echo "\n--- Vues 'show' (modèle unique) ---\n";
if ($c = Company::has('jobapplications')->first() ?? Company::first()) {
    $render('company.show', 'company.show', ['company' => $c]);
}
if ($s = School::first()) {
    $render('school.show', 'school.show', ['school' => $s]);
}
if ($jv = JobVacancy::has('jobApplications')->first() ?? JobVacancy::first()) {
    $render('job-vacancy.show', 'job-vacancy.show', ['jobVacancy' => $jv]);
}
if ($ts = TrainingSession::has('trainingApplications')->first() ?? TrainingSession::first()) {
    $render('training-session.show', 'training-session.show', ['trainingSession' => $ts]);
}
if ($ja = JobApplication::with('user')->first()) {
    $render('job-application.show', 'job-application.show', ['jobApplication' => $ja]);
    $render('job-application.edit', 'job-application.edit', ['jobApplication' => $ja]);
}
if ($ta = TrainingApplication::with('user')->first()) {
    $render('training-application.show', 'training-application.show', ['trainingApplication' => $ta]);
    $render('training-application.edit', 'training-application.edit', ['trainingApplication' => $ta]);
}
if ($u = User::first()) {
    $render('user.edit', 'user.edit', ['user' => $u]);
}

echo "\n--- Vues 'index' (paginator) ---\n";
$render('company.index', 'company.index', ['companies' => Company::paginate(5)]);
$render('school.index', 'school.index', ['schools' => School::paginate(5)]);
$render('job-vacancy.index', 'job-vacancy.index', ['jobVacancies' => JobVacancy::paginate(5)]);
$render('training-session.index', 'training-session.index', ['trainingSessions' => TrainingSession::paginate(5)]);
$render('user.index', 'user.index', ['users' => User::paginate(5)]);
$render('job-application.index', 'job-application.index', ['jobApplications' => JobApplication::paginate(5)]);
$render('training-application.index', 'training-application.index', ['trainingApplications' => TrainingApplication::paginate(5)]);

echo "\n--- Terminé ---\n";
