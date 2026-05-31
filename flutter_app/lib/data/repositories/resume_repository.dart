import 'dart:typed_data';
import 'package:dio/dio.dart';
import '../datasources/api_client.dart';
import '../models/resume/resume_model.dart';

class ResumeRepository {
  final _client = ApiClient();

  Future<List<ResumeModel>> myResumes() async {
    final res = await _client.dio.get('/resumes');
    final data = res.data as List;
    return data.map((e) => ResumeModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  /// Upload a resume. Pass either [filePath] (mobile/desktop) or [fileBytes] (web,
  /// Android scoped storage). Exactly one must be non-null.
  Future<ResumeModel> uploadResume(
    String fileName, {
    String? filePath,
    Uint8List? fileBytes,
  }) async {
    final multipart = (filePath != null && filePath.isNotEmpty)
        ? await MultipartFile.fromFile(filePath, filename: fileName)
        : MultipartFile.fromBytes(fileBytes!, filename: fileName);

    final formData = FormData.fromMap({'resume': multipart});
    final res = await _client.dio.post('/resumes', data: formData);
    return ResumeModel.fromJson(res.data as Map<String, dynamic>);
  }

  Future<void> deleteResume(String id) async {
    await _client.dio.delete('/resumes/$id');
  }
}
