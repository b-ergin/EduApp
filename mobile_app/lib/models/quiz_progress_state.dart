import 'dart:math' as math;

class QuizProgressState {
  QuizProgressState({required this.totalQuestions});

  final int totalQuestions;
  final Set<int> answeredQuestionIds = {};
  final Set<int> correctQuestionIds = {};
  int? currentQuestionId;
  bool everCompleted = false;
  int bestCorrectCount = 0;

  int get answeredCount => math.min(answeredQuestionIds.length, totalQuestions);
  int get correctCount => math.min(correctQuestionIds.length, totalQuestions);
  bool get inProgress => (currentQuestionId ?? 0) > 0;
  int get percent =>
      totalQuestions == 0
          ? 0
          : ((answeredCount / totalQuestions) * 100).round();
  int get scorePercent =>
      totalQuestions == 0 ? 0 : ((correctCount / totalQuestions) * 100).round();
  int get bestScorePercent =>
      totalQuestions == 0
          ? 0
          : ((math.min(bestCorrectCount, totalQuestions) / totalQuestions) *
                  100)
              .round();

  int get stars {
    if (!everCompleted) return 0;
    if (bestScorePercent >= 90) return 3;
    if (bestScorePercent >= 70) return 2;
    if (bestScorePercent >= 50) return 1;
    return 0;
  }

  String get status {
    if (inProgress) return 'in_progress';
    if (everCompleted) return 'completed';
    return 'not_started';
  }

  void beginRetake({required int firstQuestionId}) {
    answeredQuestionIds.clear();
    correctQuestionIds.clear();
    currentQuestionId = firstQuestionId;
  }

  void markCompleted() {
    everCompleted = true;
    currentQuestionId = null;
    if (correctCount > bestCorrectCount) {
      bestCorrectCount = correctCount;
    }
  }

  Map<String, dynamic> toJson() {
    return {
      'answered_question_ids': answeredQuestionIds.toList(),
      'correct_question_ids': correctQuestionIds.toList(),
      'current_question_id': currentQuestionId,
      'ever_completed': everCompleted,
      'best_correct_count': bestCorrectCount,
      // Legacy key kept for compatibility with older local payloads.
      'completed': everCompleted,
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
    final legacyCompleted = (json['completed'] as bool?) ?? false;
    state.everCompleted = (json['ever_completed'] as bool?) ?? legacyCompleted;
    state.bestCorrectCount =
        (json['best_correct_count'] as num?)?.toInt() ??
        (state.everCompleted ? state.correctCount : 0);

    // Normalize legacy records where all answers exist but completed flag
    // was missing and current pointer is empty.
    if (!state.inProgress &&
        state.totalQuestions > 0 &&
        state.answeredCount >= state.totalQuestions) {
      state.everCompleted = true;
      if (state.correctCount > state.bestCorrectCount) {
        state.bestCorrectCount = state.correctCount;
      }
    }

    return state;
  }
}
