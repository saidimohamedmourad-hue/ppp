class CompanyModel {
  final String id;
  final String name;
  final String? address;
  final String? industry;
  final String? website;

  const CompanyModel({required this.id, required this.name, this.address, this.industry, this.website});

  factory CompanyModel.fromJson(Map<String, dynamic> json) => CompanyModel(
    id: json['id'] as String,
    name: json['name'] as String,
    address: json['address'] as String?,
    industry: json['industry'] as String?,
    website: json['website'] as String?,
  );
}

class JobCategoryModel {
  final String id;
  final String name;

  const JobCategoryModel({required this.id, required this.name});

  factory JobCategoryModel.fromJson(Map<String, dynamic> json) => JobCategoryModel(
    id: json['id'] as String,
    name: json['name'] as String,
  );
}

class JobModel {
  final String id;
  final String title;
  final String description;
  final String location;
  final String type;
  final double salary;
  final int viewCount;
  final CompanyModel? company;
  final JobCategoryModel? jobCategory;
  final DateTime createdAt;

  const JobModel({
    required this.id,
    required this.title,
    required this.description,
    required this.location,
    required this.type,
    required this.salary,
    required this.viewCount,
    required this.createdAt,
    this.company,
    this.jobCategory,
  });

  factory JobModel.fromJson(Map<String, dynamic> json) => JobModel(
    id: json['id'] as String,
    title: json['title'] as String,
    description: json['description'] as String,
    location: json['location'] as String,
    type: json['type'] as String,
    salary: double.tryParse(json['salary'].toString()) ?? 0.0,
    viewCount: (json['viewCount'] as num?)?.toInt() ?? 0,
    createdAt: DateTime.parse(json['created_at'] as String),
    company: json['company'] != null ? CompanyModel.fromJson(json['company'] as Map<String, dynamic>) : null,
    jobCategory: json['job_category'] != null ? JobCategoryModel.fromJson(json['job_category'] as Map<String, dynamic>) : null,
  );
}

class ApplicantUserModel {
  final String id;
  final String name;
  final String email;

  const ApplicantUserModel({required this.id, required this.name, required this.email});

  factory ApplicantUserModel.fromJson(Map<String, dynamic> json) => ApplicantUserModel(
    id: json['id'].toString(),
    name: json['name'] as String,
    email: json['email'] as String,
  );
}

class JobApplicationModel {
  final String id;
  final String status;
  final int? aiGeneratedScore;
  final String? aiGeneratedFeedback;
  final JobModel? jobVacancy;
  final ApplicantUserModel? user;
  final DateTime appliedAt;

  const JobApplicationModel({
    required this.id,
    required this.status,
    required this.appliedAt,
    this.aiGeneratedScore,
    this.aiGeneratedFeedback,
    this.jobVacancy,
    this.user,
  });

  factory JobApplicationModel.fromJson(Map<String, dynamic> json) => JobApplicationModel(
    id: json['id'] as String,
    status: json['status'] as String,
    aiGeneratedScore: (json['aiGeneratedScore'] as num?)?.toInt(),
    aiGeneratedFeedback: json['aiGeneratedFeedback'] as String?,
    jobVacancy: json['job_vacancy'] != null ? JobModel.fromJson(json['job_vacancy'] as Map<String, dynamic>) : null,
    user: json['user'] != null ? ApplicantUserModel.fromJson(json['user'] as Map<String, dynamic>) : null,
    appliedAt: DateTime.parse(json['created_at'] as String),
  );
}
