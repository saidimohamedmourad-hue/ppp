import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/models/resume/resume_model.dart';
import '../../data/repositories/resume_repository.dart';

final resumeRepositoryProvider = Provider((_) => ResumeRepository());

final myResumesProvider = FutureProvider<List<ResumeModel>>(
  (ref) => ref.read(resumeRepositoryProvider).myResumes(),
);
