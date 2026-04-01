class QuizItem {
  QuizItem({
    required this.id,
    required this.title,
    required this.subject,
    required this.grade,
    required this.questionCount,
  });

  final int id;
  final String title;
  final String subject;
  final String grade;
  final int questionCount;

  factory QuizItem.fromJson(Map<String, dynamic> json) {
    return QuizItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: (json['title'] ?? 'Untitled').toString(),
      subject: (json['subject'] ?? 'N/A').toString(),
      grade: (json['grade'] ?? 'N/A').toString(),
      questionCount: (json['questions_count'] as num?)?.toInt() ?? 0,
    );
  }
}
