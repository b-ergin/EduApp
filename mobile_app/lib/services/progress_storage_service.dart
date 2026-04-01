import 'dart:convert';

import 'package:mobile_app/models/quiz_item.dart';
import 'package:mobile_app/models/quiz_progress_state.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ProgressStorageService {
  static const String progressStorageKey = 'eduapp_student_progress_v1';

  Future<void> save(Map<int, QuizProgressState> progressByQuiz) async {
    final prefs = await SharedPreferences.getInstance();
    final payload = <String, dynamic>{};
    progressByQuiz.forEach((quizId, state) {
      payload[quizId.toString()] = state.toJson();
    });
    await prefs.setString(progressStorageKey, jsonEncode(payload));
  }

  Future<Map<int, QuizProgressState>> load({
    required List<QuizItem> quizzes,
  }) async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(progressStorageKey);
    if (raw == null || raw.isEmpty) return {};

    final decoded = jsonDecode(raw);
    if (decoded is! Map<String, dynamic>) return {};

    final result = <int, QuizProgressState>{};
    for (final quiz in quizzes) {
      final quizRaw = decoded[quiz.id.toString()];
      if (quizRaw is! Map<String, dynamic>) continue;
      result[quiz.id] = QuizProgressState.fromJson(quiz.questionCount, quizRaw);
    }

    return result;
  }
}
