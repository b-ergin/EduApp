class QuizItem {
  QuizItem({
    required this.id,
    required this.title,
    required this.subject,
    required this.grade,
    required this.questionCount,
    required this.sortOrder,
    required this.isChallenge,
    this.challengeWindowSize,
    this.challengeMinStars,
    required this.xpWeight,
  });

  final int id;
  final String title;
  final String subject;
  final String grade;
  final int questionCount;
  final int sortOrder;
  final bool isChallenge;
  final int? challengeWindowSize;
  final int? challengeMinStars;
  final int xpWeight;

  factory QuizItem.fromJson(Map<String, dynamic> json) {
    return QuizItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: (json['title'] ?? 'Untitled').toString(),
      subject: (json['subject'] ?? 'N/A').toString(),
      grade: (json['grade'] ?? 'N/A').toString(),
      questionCount: (json['questions_count'] as num?)?.toInt() ?? 0,
      sortOrder:
          (json['sort_order'] as num?)?.toInt() ??
          (json['id'] as num?)?.toInt() ??
          0,
      isChallenge: (json['is_challenge'] as bool?) ?? false,
      challengeWindowSize: (json['challenge_window_size'] as num?)?.toInt(),
      challengeMinStars: (json['challenge_min_stars'] as num?)?.toInt(),
      xpWeight: ((json['xp_weight'] as num?)?.toInt() ?? 3).clamp(1, 10),
    );
  }
}
