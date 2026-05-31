import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/models/job/job_model.dart';
import '../../data/repositories/job_repository.dart';

final jobRepositoryProvider = Provider((_) => JobRepository());

typedef JobListFilters = ({String? search, String? type, String? category});

final jobListProvider = FutureProvider.family<List<JobModel>, JobListFilters>(
  (ref, filters) => ref.read(jobRepositoryProvider).getJobs(
    search: filters.search,
    type: filters.type,
    category: filters.category,
  ),
);

final jobDetailProvider = FutureProvider.family<JobModel, String>(
  (ref, id) => ref.read(jobRepositoryProvider).getJob(id),
);

final myJobApplicationsProvider = FutureProvider<List<JobApplicationModel>>(
  (ref) => ref.read(jobRepositoryProvider).myApplications(),
);

final companyJobsProvider = FutureProvider<List<JobModel>>(
  (ref) => ref.read(jobRepositoryProvider).companyJobs(),
);

final jobApplicantsProvider = FutureProvider.family<List<JobApplicationModel>, String>(
  (ref, jobId) => ref.read(jobRepositoryProvider).jobApplicants(jobId),
);

final jobCategoriesProvider = FutureProvider<List<JobCategoryModel>>(
  (ref) => ref.read(jobRepositoryProvider).getJobCategories(),
);
