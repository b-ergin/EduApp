import 'package:mobile_app/models/choice_item.dart';

class QuestionPayload {
  QuestionPayload({
    required this.quizId,
    required this.quizTitle,
    required this.questionId,
    required this.questionText,
    required this.imagePath,
    required this.choices,
    required this.currentIndex,
    required this.totalQuestions,
    required this.percent,
  });

  final int quizId;
  final String quizTitle;
  final int questionId;
  final String questionText;
  final String? imagePath;
  final List<ChoiceItem> choices;
  final int currentIndex;
  final int totalQuestions;
  final int percent;

  factory QuestionPayload.fromApi(Map<String, dynamic> data) {
    final question = data['question'] as Map<String, dynamic>? ?? {};
    final quiz = data['quiz'] as Map<String, dynamic>? ?? {};
    final progress = data['progress'] as Map<String, dynamic>? ?? {};

    return QuestionPayload(
      quizId: (quiz['id'] as num?)?.toInt() ?? 0,
      quizTitle: (quiz['title'] ?? 'Quiz').toString(),
      questionId: (question['id'] as num?)?.toInt() ?? 0,
      questionText: (question['question_text'] ?? '').toString(),
      imagePath: question['image_path']?.toString(),
      choices:
          ((question['choices'] as List?) ?? [])
              .map((item) => ChoiceItem.fromJson(item as Map<String, dynamic>))
              .toList(),
      currentIndex: (progress['current_index'] as num?)?.toInt() ?? 0,
      totalQuestions: (progress['total_questions'] as num?)?.toInt() ?? 0,
      percent: (progress['percent'] as num?)?.toInt() ?? 0,
    );
  }
}
