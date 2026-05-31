import 'dart:typed_data';

import 'package:dio/dio.dart';
import '../datasources/api_client.dart';
import '../models/job/job_model.dart';

class JobRepository {
  final _client = ApiClient();

  Future<List<JobModel>> getJobs({String? search, String? type, String? category}) async {
    final res = await _client.dio.get('/jobs', queryParameters: {
      if (search != null && search.isNotEmpty) 'search': search,
      if (type != null) 'type': type,
      if (category != null) 'category': category,
    });
    final data = res.data['data'] as List;
    return data.map((e) => JobModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<JobModel> getJob(String id) async {
    final res = await _client.dio.get('/jobs/$id');
    return JobModel.fromJson(res.data as Map<String, dynamic>);
  }

  Future<void> applyJob(
    String jobId, {
    String? resumeId,
    String? filePath,
    String? fileName,
    Uint8List? fileBytes,
    String? phone,
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
      await _client.dio.post('/jobs/$jobId/apply', data: FormData.fromMap({
        'resume_file': part,
        if (phone != null && phone.isNotEmpty) 'phone': phone,
      }));
    } else {
      await _client.dio.post('/jobs/$jobId/apply', data: {
        'resume_id': resumeId,
        if (phone != null && phone.isNotEmpty) 'phone': phone,
      });
    }
  }

  Future<List<JobApplicationModel>> myApplications() async {
    final res = await _client.dio.get('/my/job-applications');
    final data = res.data['data'] as List;
    return data.map((e) => JobApplicationModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  // Company-owner
  Future<List<JobModel>> companyJobs() async {
    final res = await _client.dio.get('/company/jobs');
    final data = res.data['data'] as List;
    return data.map((e) => JobModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<JobModel> createJob(Map<String, dynamic> data) async {
    final res = await _client.dio.post('/company/jobs', data: data);
    return JobModel.fromJson(res.data as Map<String, dynamic>);
  }

  Future<JobModel> updateJob(String id, Map<String, dynamic> data) async {
    final res = await _client.dio.put('/company/jobs/$id', data: data);
    return JobModel.fromJson(res.data as Map<String, dynamic>);
  }

  Future<List<JobCategoryModel>> getJobCategories() async {
    final res = await _client.dio.get('/job-categories');
    final data = res.data['data'] as List;
    return data.map((e) => JobCategoryModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<void> deleteJob(String id) async {
    await _client.dio.delete('/company/jobs/$id');
  }

  Future<List<JobApplicationModel>> jobApplicants(String jobId) async {
    final res = await _client.dio.get('/company/jobs/$jobId/applicants');
    final data = res.data['data'] as List;
    return data.map((e) => JobApplicationModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<void> updateApplicationStatus(String applicationId, String status) async {
    await _client.dio.put('/company/applications/$applicationId/status', data: {'status': status});
  }
}
