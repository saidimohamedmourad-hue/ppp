<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Niveaux d'études (Algérie)
    |--------------------------------------------------------------------------
    |
    | Liste fermée des niveaux proposés au candidat lors d'une candidature
    | (emploi) ou d'une inscription (formation). Sert de source de vérité pour
    | la validation côté API et est exposée via GET /api/config aux clients
    | (web React + Flutter) afin que la liste reste alignée partout.
    |
    */

    'levels' => [
        'Primaire',
        'Moyen (BEM)',
        'Secondaire',
        'Baccalauréat',
        'Formation professionnelle',
        'Licence',
        'Master',
        'Ingéniorat',
        'Doctorat',
        'Autre',
    ],

];
