// Liste fermée des niveaux d'études (Algérie).
// Doit rester alignée avec le backend : job-backoffice/config/education.php
// (également exposée via GET /api/config -> education_levels).
export const EDUCATION_LEVELS = [
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
] as const

export type EducationLevel = (typeof EDUCATION_LEVELS)[number]
