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
  });

  final QuizItem quiz;
  final QuizProgressState progress;
  final bool unlocked;
  final VoidCallback? onStart;

  @override
  Widget build(BuildContext context) {
    final status = progress.status;
    final badge =
        status == 'completed'
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
      child: Card(
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        margin: const EdgeInsets.only(bottom: 8),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(14),
          side: const BorderSide(color: Color(0xFFDCE5F2)),
        ),
        child: Padding(
          padding: const EdgeInsets.all(10),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                quiz.title,
                style: const TextStyle(
                  fontWeight: FontWeight.w700,
                  fontSize: 16,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                '${quiz.subject} • ${quiz.grade} • ${quiz.questionCount} questions',
                style: const TextStyle(color: Color(0xFF6B7280), fontSize: 13),
              ),
              const SizedBox(height: 8),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [badge, StarRow(stars: progress.stars)],
              ),
              const SizedBox(height: 8),
              ClipRRect(
                borderRadius: BorderRadius.circular(999),
                child: LinearProgressIndicator(
                  value: progress.percent / 100,
                  minHeight: 8,
                  backgroundColor: const Color(0xFFE5E7EB),
                  color: const Color(0xFF14B8A6),
                ),
              ),
              const SizedBox(height: 6),
              Text(
                '${progress.answeredCount}/${quiz.questionCount} answered (${progress.percent}%) • ${progress.correctCount} correct (${progress.scorePercent}%)',
                style: const TextStyle(color: Color(0xFF6B7280), fontSize: 12),
              ),
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
