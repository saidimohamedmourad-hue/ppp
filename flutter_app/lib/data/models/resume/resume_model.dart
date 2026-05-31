class ResumeModel {
  final String id;
  final String filename;
  final String fileUri;
  final DateTime createdAt;

  const ResumeModel({
    required this.id,
    required this.filename,
    required this.fileUri,
    required this.createdAt,
  });

  factory ResumeModel.fromJson(Map<String, dynamic> json) => ResumeModel(
    id: json['id'] as String,
    filename: json['filename'] as String,
    fileUri: json['fileUri'] as String,
    createdAt: DateTime.parse(json['created_at'] as String),
  );
}
