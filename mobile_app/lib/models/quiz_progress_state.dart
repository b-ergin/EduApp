import 'dart:math' as math;

class QuizProgressState {
  QuizProgressState({required this.totalQuestions});

  final int totalQuestions;
  final Set<int> answeredQuestionIds = {};
  final Set<int> correctQuestionIds = {};
  int? currentQuestionId;
  bool completed = false;

  int get answeredCount => math.min(answeredQuestionIds.length, totalQuestions);
  int get correctCount => math.min(correctQuestionIds.length, totalQuestions);
  int get percent =>
      totalQuestions == 0
          ? 0
          : ((answeredCount / totalQuestions) * 100).round();
  int get scorePercent =>
      totalQuestions == 0 ? 0 : ((correctCount / totalQuestions) * 100).round();

  int get stars {
    if (!completed) return 0;
    if (scorePercent >= 90) return 3;
    if (scorePercent >= 70) return 2;
    if (scorePercent >= 50) return 1;
    return 0;
  }

  String get status {
    if (completed) return 'completed';
    if (answeredQuestionIds.isNotEmpty) return 'in_progress';
    return 'not_started';
  }

  Map<String, dynamic> toJson() {
    return {
      'answered_question_ids': answeredQuestionIds.toList(),
      'correct_question_ids': correctQuestionIds.toList(),
      'current_question_id': currentQuestionId,
      'completed': completed,
    };
  }

  static QuizProgressState fromJson(
    int totalQuestions,
    Map<String, dynamic> json,
  ) {
    final state = QuizProgressState(totalQuestions: totalQuestions);

    final answered = (json['answered_question_ids'] as List? ?? []).map(
      (id) => (id as num).toInt(),
    );
    final correct = (json['correct_question_ids'] as List? ?? []).map(
      (id) => (id as num).toInt(),
    );

    state.answeredQuestionIds.addAll(answered);
    state.correctQuestionIds.addAll(correct);
    state.currentQuestionId = (json['current_question_id'] as num?)?.toInt();
    state.completed = (json['completed'] as bool?) ?? false;

    return state;
  }
}
