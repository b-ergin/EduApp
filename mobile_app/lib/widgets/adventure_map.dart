import 'package:flutter/material.dart';
import 'package:mobile_app/models/quiz_item.dart';
import 'package:mobile_app/models/quiz_progress_state.dart';
import 'package:mobile_app/widgets/star_row.dart';

class AdventureMap extends StatelessWidget {
  const AdventureMap({
    super.key,
    required this.quizzes,
    required this.allQuizzes,
    required this.progressByQuiz,
    required this.isUnlocked,
    required this.onNodeTap,
  });

  final List<QuizItem> quizzes;
  final List<QuizItem> allQuizzes;
  final Map<int, QuizProgressState> progressByQuiz;
  final bool Function(QuizItem quiz) isUnlocked;
  final void Function(QuizItem quiz) onNodeTap;

  @override
  Widget build(BuildContext context) {
    if (quizzes.isEmpty) {
      return const SizedBox.shrink();
    }

    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFDCE5F2)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x10111A2B),
            blurRadius: 10,
            offset: Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Adventure Path',
            style: TextStyle(fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 2),
          const Text(
            'Complete quizzes in order to unlock the next node.',
            style: TextStyle(fontSize: 12, color: Color(0xFF6B7280)),
          ),
          const SizedBox(height: 8),
          SizedBox(
            height: 140,
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: SizedBox(
                width: quizzes.length * 108,
                child: Stack(
                  children: [
                    Positioned.fill(
                      child: CustomPaint(
                        painter: _MapLinesPainter(nodeCount: quizzes.length),
                      ),
                    ),
                    Row(
                      children: [
                        for (int i = 0; i < quizzes.length; i++)
                          SizedBox(
                            width: 108,
                            child: _MapNodeWidget(
                              number: _quizOrderNumber(quizzes[i]),
                              topOffset: i.isEven ? 4 : 34,
                              stars: progressByQuiz[quizzes[i].id]?.stars ?? 0,
                              status:
                                  progressByQuiz[quizzes[i].id]?.status ??
                                  'not_started',
                              unlocked: isUnlocked(quizzes[i]),
                              title: quizzes[i].title,
                              onTap:
                                  isUnlocked(quizzes[i])
                                      ? () => onNodeTap(quizzes[i])
                                      : null,
                            ),
                          ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  int _quizOrderNumber(QuizItem quiz) {
    final index = allQuizzes.indexWhere((item) => item.id == quiz.id);
    return index >= 0 ? index + 1 : 0;
  }
}

class _MapLinesPainter extends CustomPainter {
  _MapLinesPainter({required this.nodeCount});

  final int nodeCount;

  @override
  void paint(Canvas canvas, Size size) {
    final paint =
        Paint()
          ..color = const Color(0xFFCBD5E1)
          ..style = PaintingStyle.stroke
          ..strokeWidth = 5
          ..strokeCap = StrokeCap.round;

    for (int i = 0; i < nodeCount - 1; i++) {
      final x1 = 54.0 + (i * 108.0);
      final x2 = 54.0 + ((i + 1) * 108.0);
      final y1 = i.isEven ? 36.0 : 66.0;
      final y2 = (i + 1).isEven ? 36.0 : 66.0;
      final path =
          Path()
            ..moveTo(x1, y1)
            ..quadraticBezierTo((x1 + x2) / 2, (y1 + y2) / 2 - 10, x2, y2);
      canvas.drawPath(path, paint);
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class _MapNodeWidget extends StatelessWidget {
  const _MapNodeWidget({
    required this.number,
    required this.topOffset,
    required this.stars,
    required this.status,
    required this.unlocked,
    required this.title,
    required this.onTap,
  });

  final int number;
  final double topOffset;
  final int stars;
  final String status;
  final bool unlocked;
  final String title;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    Color bg = const Color(0xFFDBEAFE);
    Color border = const Color(0xFF60A5FA);
    Color text = const Color(0xFF1E3A8A);

    if (!unlocked) {
      bg = const Color(0xFFF3F4F6);
      border = const Color(0xFFD1D5DB);
      text = const Color(0xFF6B7280);
    } else if (status == 'completed') {
      bg = const Color(0xFFDCFCE7);
      border = const Color(0xFF22C55E);
      text = const Color(0xFF166534);
    } else if (status == 'in_progress') {
      bg = const Color(0xFFFEF3C7);
      border = const Color(0xFFF59E0B);
      text = const Color(0xFF92400E);
    }

    final stateText =
        !unlocked
            ? 'LOCK'
            : status == 'completed'
            ? 'DONE'
            : status == 'in_progress'
            ? 'NOW'
            : 'GO';
    final labelTop = topOffset + 62;
    final starsTop = labelTop + 18;

    return Align(
      alignment: Alignment.topCenter,
      child: SizedBox(
        width: 100,
        height: 136,
        child: Stack(
          alignment: Alignment.topCenter,
          children: [
            Positioned(
              top: topOffset,
              child: GestureDetector(
                onTap: onTap,
                child: Container(
                  width: 58,
                  height: 58,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: bg,
                    border: Border.all(color: border, width: 3),
                  ),
                  alignment: Alignment.center,
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        unlocked ? '$number' : '🔒',
                        style: TextStyle(
                          fontWeight: FontWeight.w800,
                          color: text,
                          fontSize: 16,
                        ),
                      ),
                      Text(
                        stateText,
                        style: TextStyle(
                          fontWeight: FontWeight.w700,
                          color: text,
                          fontSize: 8,
                          letterSpacing: 0.4,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
            Positioned(
              top: labelTop,
              child: SizedBox(
                width: 88,
                child: Text(
                  title,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ),
            Positioned(top: starsTop, child: StarRow(stars: stars)),
          ],
        ),
      ),
    );
  }
}
