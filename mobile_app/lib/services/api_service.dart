import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:mobile_app/models/answer_result.dart';
import 'package:mobile_app/models/question_payload.dart';
import 'package:mobile_app/models/quiz_item.dart';

class ApiService {
  static const String configuredBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: '',
  );

  String get baseUrl {
    if (configuredBaseUrl.isNotEmpty) {
      return configuredBaseUrl;
    }
    if (kIsWeb) {
      return 'http://127.0.0.1:8000';
    }
    if (defaultTargetPlatform == TargetPlatform.android) {
      return 'http://10.0.2.2:8000';
    }
    return 'http://127.0.0.1:8000';
  }

  Future<String> login({
    required String email,
    required String password,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/api/v1/login'),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({'email': email, 'password': password}),
    );

    final data = jsonDecode(response.body);
    if (response.statusCode == 200 && data['token'] != null) {
      return data['token'].toString();
    }

    throw Exception(
      data['message'] ?? 'Login failed (HTTP ${response.statusCode})',
    );
  }

  Future<List<QuizItem>> fetchQuizzes({required String token}) async {
    final response = await http.get(
      Uri.parse('$baseUrl/api/v1/quizzes'),
      headers: {'Accept': 'application/json', 'Authorization': 'Bearer $token'},
    );

    final data = jsonDecode(response.body);
    if (response.statusCode == 200 && data['data'] is List) {
      return (data['data'] as List)
          .map((item) => QuizItem.fromJson(item as Map<String, dynamic>))
          .toList();
    }

    throw Exception(data['message'] ?? 'Could not load quizzes');
  }

  Future<int> startQuiz({required String token, required int quizId}) async {
    final response = await http.get(
      Uri.parse('$baseUrl/api/v1/quizzes/$quizId/start'),
      headers: {'Accept': 'application/json', 'Authorization': 'Bearer $token'},
    );

    final data = jsonDecode(response.body);
    if (response.statusCode == 200) {
      final firstQuestionId =
          (data['data']['first_question_id'] as num?)?.toInt();
      if (firstQuestionId != null) return firstQuestionId;
    }

    throw Exception(data['message'] ?? 'Could not start quiz');
  }

  Future<QuestionPayload> fetchQuestion({
    required String token,
    required int quizId,
    required int questionId,
  }) async {
    final response = await http.get(
      Uri.parse('$baseUrl/api/v1/quizzes/$quizId/questions/$questionId'),
      headers: {'Accept': 'application/json', 'Authorization': 'Bearer $token'},
    );

    final data = jsonDecode(response.body);
    if (response.statusCode == 200 && data['data'] != null) {
      return QuestionPayload.fromApi(data['data'] as Map<String, dynamic>);
    }

    throw Exception(data['message'] ?? 'Could not load question');
  }

  Future<AnswerResult> submitAnswer({
    required String token,
    required int questionId,
    required int choiceId,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/api/v1/questions/$questionId/answer'),
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({'choice_id': choiceId}),
    );

    final data = jsonDecode(response.body);
    if (response.statusCode == 200 && data['data'] != null) {
      return AnswerResult.fromApi(data['data'] as Map<String, dynamic>);
    }

    throw Exception(data['message'] ?? 'Could not submit answer');
  }
}
