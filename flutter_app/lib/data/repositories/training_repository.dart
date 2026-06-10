import 'dart:typed_data';

import 'package:dio/dio.dart';
import '../datasources/api_client.dart';
import '../models/training/training_model.dart';

class TrainingRepository {
  final _client = ApiClient();

  Future<List<TrainingSessionModel>> getSessions({String? search, String? category, String? type}) async {
    final res = await _client.dio.get('/training-sessions', queryParameters: {
      if (search != null && search.isNotEmpty) 'search': search,
      if (category != null) 'category': category,
      if (type != null && type.isNotEmpty) 'type': type,
    });
    final data = res.data['data'] as List;
    return data.map((e) => TrainingSessionModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<TrainingSessionModel> getSession(String id) async {
    final res = await _client.dio.get('/training-sessions/$id');
    return TrainingSessionModel.fromJson(res.data as Map<String, dynamic>);
  }

  Future<void> applySession(
    String sessionId, {
    String? resumeId,
    String? filePath,
    String? fileName,
    Uint8List? fileBytes,
    String? phone,
    String? educationLevel,
  }) async {
    final hasFile = fileName != null &&
        ((filePath != null && filePath.isNotEmpty) ||
            (fileBytes != null && fileBytes.isNotEmpty));
    if (hasFile) {
      final MultipartFile part;
      if (filePath != null && filePath.isNotEmpty) {
        part = await MultipartFile.fromFile(filePath, filename: fileName);
      } else {
        part = MultipartFile.fromBytes(fileBytes!, filename: fileName);
      }
      await _client.dio.post('/training-sessions/$sessionId/apply', data: FormData.fromMap({
        'resume_file': part,
        if (phone != null && phone.isNotEmpty) 'phone': phone,
        if (educationLevel != null && educationLevel.isNotEmpty) 'education_level': educationLevel,
      }));
    } else {
      // CV facultatif pour une formation : n'envoyer resume_id que s'il existe.
      await _client.dio.post('/training-sessions/$sessionId/apply', data: {
        if (resumeId != null) 'resume_id': resumeId,
        if (phone != null && phone.isNotEmpty) 'phone': phone,
        if (educationLevel != null && educationLevel.isNotEmpty) 'education_level': educationLevel,
      });
    }
  }

  Future<List<TrainingApplicationModel>> myApplications() async {
    final res = await _client.dio.get('/my/training-applications');
    final data = res.data['data'] as List;
    return data.map((e) => TrainingApplicationModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<List<TrainingCategoryModel>> getTrainingCategories() async {
    final res = await _client.dio.get('/training-categories');
    final data = res.data['data'] as List;
    return data.map((e) => TrainingCategoryModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  // School-owner
  Future<List<TrainingSessionModel>> schoolSessions() async {
    final res = await _client.dio.get('/school/training-sessions');
    final data = res.data['data'] as List;
    return data.map((e) => TrainingSessionModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<TrainingSessionModel> createSession(Map<String, dynamic> data) async {
    final res = await _client.dio.post('/school/training-sessions', data: data);
    return TrainingSessionModel.fromJson(res.data as Map<String, dynamic>);
  }

  Future<TrainingSessionModel> updateSession(String id, Map<String, dynamic> data) async {
    final res = await _client.dio.put('/school/training-sessions/$id', data: data);
    return TrainingSessionModel.fromJson(res.data as Map<String, dynamic>);
  }

  Future<void> deleteSession(String id) async {
    await _client.dio.delete('/school/training-sessions/$id');
  }

  Future<List<TrainingApplicationModel>> sessionApplicants(String sessionId) async {
    final res = await _client.dio.get('/school/training-sessions/$sessionId/applicants');
    final data = res.data['data'] as List;
    return data.map((e) => TrainingApplicationModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<void> updateApplicationStatus(String applicationId, String status) async {
    await _client.dio.put('/school/training-applications/$applicationId/status', data: {'status': status});
  }
}
