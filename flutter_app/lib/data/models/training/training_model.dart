class SchoolModel {
  final String id;
  final String name;
  final String? address;
  final String? description;

  const SchoolModel({required this.id, required this.name, this.address, this.description});

  factory SchoolModel.fromJson(Map<String, dynamic> json) => SchoolModel(
    id: json['id'] as String,
    name: json['name'] as String,
    address: json['address'] as String?,
    description: json['description'] as String?,
  );
}

class TrainingCategoryModel {
  final String id;
  final String name;

  const TrainingCategoryModel({required this.id, required this.name});

  factory TrainingCategoryModel.fromJson(Map<String, dynamic> json) => TrainingCategoryModel(
    id: json['id'] as String,
    name: json['name'] as String,
  );
}

/// Training format/type. Backend values: en_ligne | accelerer | presentiel | longue_duree.
enum TrainingType {
  enLigne('en_ligne', 'En ligne'),
  accelerer('accelerer', 'Accéléré'),
  presentiel('presentiel', 'Présentiel'),
  longueDuree('longue_duree', 'Longue durée');

  final String value;
  final String label;
  const TrainingType(this.value, this.label);

  static TrainingType fromValue(String? v) {
    return TrainingType.values.firstWhere(
      (t) => t.value == v,
      orElse: () => TrainingType.presentiel,
    );
  }
}

class TrainingSessionModel {
  final String id;
  final String title;
  final String description;
  final String location;
  final String status;
  final TrainingType type;
  final String? cancellationReason;
  final int maxParticipants;
  final int currentParticipants;
  final bool isFullFromApi;
  final double? salary;
  final int viewCount;
  final DateTime trainingDate;
  final DateTime? endDate;
  final SchoolModel? school;
  final TrainingCategoryModel? trainingCategory;
  final String? minEducationLevel;
  final DateTime createdAt;

  const TrainingSessionModel({
    required this.id,
    required this.title,
    required this.description,
    required this.location,
    required this.status,
    required this.type,
    required this.maxParticipants,
    required this.currentParticipants,
    required this.trainingDate,
    required this.viewCount,
    required this.createdAt,
    this.cancellationReason,
    this.isFullFromApi = false,
    this.salary,
    this.endDate,
    this.school,
    this.trainingCategory,
    this.minEducationLevel,
  });

  // Use the API-provided `is_full` flag when present, otherwise derive locally.
  bool get isFull =>
      isFullFromApi || (maxParticipants > 0 && currentParticipants >= maxParticipants);
  bool get isCancelled => status == 'cancelled';

  factory TrainingSessionModel.fromJson(Map<String, dynamic> json) => TrainingSessionModel(
    id: json['id'] as String,
    title: json['title'] as String,
    description: json['description'] as String,
    location: json['location'] as String,
    status: json['status'] as String,
    type: TrainingType.fromValue(json['type'] as String?),
    cancellationReason: json['cancellation_reason'] as String?,
    maxParticipants: (json['maxParticipants'] as num).toInt(),
    currentParticipants: (json['currentParticipants'] as num?)?.toInt() ?? 0,
    isFullFromApi: json['is_full'] as bool? ?? false,
    salary: json['salary'] != null ? (double.tryParse(json['salary'].toString()) ?? 0.0) : null,
    viewCount: (json['viewCount'] as num?)?.toInt() ?? 0,
    trainingDate: DateTime.parse(json['trainingDate'] as String),
    endDate: json['endDate'] != null ? DateTime.parse(json['endDate'] as String) : null,
    createdAt: DateTime.parse(json['created_at'] as String),
    school: json['school'] != null ? SchoolModel.fromJson(json['school'] as Map<String, dynamic>) : null,
    trainingCategory: json['training_category'] != null
        ? TrainingCategoryModel.fromJson(json['training_category'] as Map<String, dynamic>)
        : null,
    minEducationLevel: json['min_education_level'] as String?,
  );
}

class TrainingApplicantUserModel {
  final String id;
  final String name;
  final String email;

  const TrainingApplicantUserModel({required this.id, required this.name, required this.email});

  factory TrainingApplicantUserModel.fromJson(Map<String, dynamic> json) => TrainingApplicantUserModel(
    id: json['id'].toString(),
    name: json['name'] as String,
    email: json['email'] as String,
  );
}

class TrainingApplicationModel {
  final String id;
  final String status;
  final bool isWaitlist;
  final int? aiGeneratedScore;
  final String? aiGeneratedFeedback;
  final TrainingSessionModel? trainingSession;
  final TrainingApplicantUserModel? user;
  final DateTime appliedAt;

  const TrainingApplicationModel({
    required this.id,
    required this.status,
    required this.appliedAt,
    this.isWaitlist = false,
    this.aiGeneratedScore,
    this.aiGeneratedFeedback,
    this.trainingSession,
    this.user,
  });

  factory TrainingApplicationModel.fromJson(Map<String, dynamic> json) => TrainingApplicationModel(
    id: json['id'] as String,
    status: json['status'] as String,
    isWaitlist: json['is_waitlist'] as bool? ?? false,
    aiGeneratedScore: (json['aiGeneratedScore'] as num?)?.toInt(),
    aiGeneratedFeedback: json['aiGeneratedFeedback'] as String?,
    trainingSession: json['training_session'] != null
        ? TrainingSessionModel.fromJson(json['training_session'] as Map<String, dynamic>)
        : null,
    user: json['user'] != null ? TrainingApplicantUserModel.fromJson(json['user'] as Map<String, dynamic>) : null,
    appliedAt: DateTime.parse(json['created_at'] as String),
  );
}
