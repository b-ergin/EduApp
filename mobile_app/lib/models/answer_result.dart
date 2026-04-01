class AnswerResult {
  AnswerResult({
    required this.isCorrect,
    required this.selectedChoiceId,
    required this.correctChoiceId,
    required this.nextQuestionId,
    required this.finished,
  });

  final bool isCorrect;
  final int selectedChoiceId;
  final int? correctChoiceId;
  final int? nextQuestionId;
  final bool finished;

  factory AnswerResult.fromApi(Map<String, dynamic> data) {
    return AnswerResult(
      isCorrect: (data['is_correct'] as bool?) ?? false,
      selectedChoiceId: (data['selected_choice_id'] as num?)?.toInt() ?? 0,
      correctChoiceId: (data['correct_choice_id'] as num?)?.toInt(),
      nextQuestionId: (data['next_question_id'] as num?)?.toInt(),
      finished: (data['finished'] as bool?) ?? false,
    );
  }
}
