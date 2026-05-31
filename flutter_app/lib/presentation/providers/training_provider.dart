import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/models/training/training_model.dart';
import '../../data/repositories/training_repository.dart';

final trainingCategoriesProvider = FutureProvider<List<TrainingCategoryModel>>(
  (ref) => ref.read(trainingRepositoryProvider).getTrainingCategories(),
);

final trainingRepositoryProvider = Provider((_) => TrainingRepository());

typedef TrainingListFilters = ({String? search, String? category, String? type});

final trainingListProvider = FutureProvider.family<List<TrainingSessionModel>, TrainingListFilters>(
  (ref, filters) => ref.read(trainingRepositoryProvider).getSessions(
    search: filters.search,
    category: filters.category,
    type: filters.type,
  ),
);

final trainingDetailProvider = FutureProvider.family<TrainingSessionModel, String>(
  (ref, id) => ref.read(trainingRepositoryProvider).getSession(id),
);

final myTrainingApplicationsProvider = FutureProvider<List<TrainingApplicationModel>>(
  (ref) => ref.read(trainingRepositoryProvider).myApplications(),
);

final schoolSessionsProvider = FutureProvider<List<TrainingSessionModel>>(
  (ref) => ref.read(trainingRepositoryProvider).schoolSessions(),
);
