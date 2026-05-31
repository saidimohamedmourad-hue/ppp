class UserModel {
  final String id;
  final String name;
  final String email;
  final String? phone;
  final String role;
  final String? profilePicture;
  final String? cvUrl;
  final DateTime? lastLoginAt;
  final bool hasCompany;
  final bool hasSchool;

  const UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.phone,
    this.profilePicture,
    this.cvUrl,
    this.lastLoginAt,
    this.hasCompany = false,
    this.hasSchool = false,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) => UserModel(
    id: json['id'].toString(),
    name: json['name'] as String,
    email: json['email'] as String,
    phone: json['phone'] as String?,
    role: json['role'] as String,
    profilePicture: json['profile_picture'] as String?,
    cvUrl: json['cv_url'] as String?,
    lastLoginAt: json['last_login_at'] != null
        ? DateTime.parse(json['last_login_at'] as String)
        : null,
    hasCompany: json['has_company'] as bool? ?? false,
    hasSchool: json['has_school'] as bool? ?? false,
  );

  /// Cheap "do I need to ask the user for a phone before they apply?" check.
  bool get hasPhone => phone != null && phone!.trim().isNotEmpty;

  bool get isJobSeeker   => role == 'job-seeker';
  bool get isCompany     => role == 'company-owner';
  bool get isSchoolOwner => role == 'school-owner';
  bool get isAdmin       => role == 'admin';
}
