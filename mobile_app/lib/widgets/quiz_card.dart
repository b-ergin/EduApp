import 'package:flutter/material.dart';
import 'package:mobile_app/models/quiz_item.dart';
import 'package:mobile_app/models/quiz_progress_state.dart';
import 'package:mobile_app/widgets/star_row.dart';
import 'package:mobile_app/widgets/status_badge.dart';

class QuizCard extends StatelessWidget {
  const QuizCard({
    super.key,
    required this.quiz,
    required this.progress,
    required this.unlocked,
    required this.onStart,
    this.unlockReason,
    this.challengeEarnedStars,
    this.challengeRequiredStars,
  });

  final QuizItem quiz;
  final QuizProgressState progress;
  final bool unlocked;
  final VoidCallback? onStart;
  final String? unlockReason;
  final int? challengeEarnedStars;
  final int? challengeRequiredStars;

  @override
  Widget build(BuildContext context) {
    final status = unlocked ? progress.status : 'locked';
    final badge =
        status == 'locked'
            ? const StatusBadge(
              label: 'Locked',
              bg: Color(0xFFF3F4F6),
              border: Color(0xFFD1D5DB),
              text: Color(0xFF6B7280),
            )
            : status == 'completed'
            ? const StatusBadge(
              label: 'Completed',
              bg: Color(0xFFDCFCE7),
              border: Color(0xFF86EFAC),
              text: Color(0xFF166534),
            )
            : status == 'in_progress'
            ? const StatusBadge(
              label: 'In Progress',
              bg: Color(0xFFFFEDD5),
              border: Color(0xFFFDBA74),
              text: Color(0xFF9A3412),
            )
            : const StatusBadge(
              label: 'Not Started',
              bg: Color(0xFFDBEAFE),
              border: Color(0xFF93C5FD),
              text: Color(0xFF1E3A8A),
            );

    return Opacity(
      opacity: unlocked ? 1 : 0.7,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 260),
        curve: Curves.easeOutCubic,
        margin: const EdgeInsets.only(bottom: 8),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: const Color(0xFFDCE5F2)),
          color: Colors.white,
          boxShadow:
              unlocked
                  ? const [
                    BoxShadow(
                      color: Color(0x11111A2B),
                      blurRadius: 10,
                      offset: Offset(0, 4),
                    ),
                  ]
                  : null,
        ),
        child: Padding(
          padding: const EdgeInsets.all(10),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(
                      quiz.title,
                      style: const TextStyle(
                        fontWeight: FontWeight.w700,
                        fontSize: 18,
                      ),
                    ),
                  ),
                  StarRow(stars: unlocked ? progress.stars : 0),
                ],
              ),
              const SizedBox(height: 4),
              Text(
                '${quiz.subject} • ${quiz.grade} • ${quiz.questionCount} questions',
                style: const TextStyle(
                  color: Color(0xFF6B7280),
                  fontSize: 12.5,
                ),
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  badge,
                  if (quiz.isChallenge) ...[
                    const SizedBox(width: 8),
                    const StatusBadge(
                      label: 'Challenge',
                      bg: Color(0xFFFEF3C7),
                      border: Color(0xFFFDE68A),
                      text: Color(0xFF92400E),
                    ),
                  ],
                ],
              ),
              const SizedBox(height: 8),
              ClipRRect(
                borderRadius: BorderRadius.circular(999),
                child: LinearProgressIndicator(
                  value: unlocked ? (progress.percent / 100) : 0,
                  minHeight: 8,
                  backgroundColor: const Color(0xFFE5E7EB),
                  color: const Color(0xFF14B8A6),
                ),
              ),
              const SizedBox(height: 6),
              Text(
                unlocked
                    ? '${progress.answeredCount}/${quiz.questionCount} answered • ${progress.correctCount} correct'
                    : (unlockReason ??
                        'Locked until previous quizzes are completed.'),
                style: const TextStyle(color: Color(0xFF6B7280), fontSize: 12),
              ),
              if (!unlocked &&
                  challengeEarnedStars != null &&
                  challengeRequiredStars != null) ...[
                const SizedBox(height: 4),
                Text(
                  'Challenge stars: $challengeEarnedStars / $challengeRequiredStars',
                  style: const TextStyle(
                    color: Color(0xFF9A3412),
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
              const SizedBox(height: 8),
              SizedBox(
                width: double.infinity,
                child: FilledButton(
                  onPressed: onStart,
                  style: FilledButton.styleFrom(
                    backgroundColor:
                        unlocked
                            ? const Color(0xFF0F766E)
                            : const Color(0xFF9CA3AF),
                  ),
                  child: Text(
                    !unlocked
                        ? 'Locked'
                        : status == 'completed'
                        ? 'Retake Quiz'
                        : status == 'in_progress'
                        ? 'Resume Quiz'
                        : 'Start Quiz',
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
