import 'package:flutter/material.dart';
import 'package:mobile_app/models/quiz_item.dart';
import 'package:mobile_app/models/quiz_progress_state.dart';

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

  static const double _slotWidth = 128;
  static const double _nodeImageWidth = 98;
  static const double _nodeImageHeight = 78;
  static const double _mapHeight = 188;
  static const double _mapSidePadding = 16;

  @override
  Widget build(BuildContext context) {
    if (quizzes.isEmpty) return const SizedBox.shrink();

    final mapWidth = (quizzes.length * _slotWidth) + (_mapSidePadding * 2);
    final completedCount =
        quizzes
            .where((q) => (progressByQuiz[q.id]?.everCompleted ?? false))
            .length;

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
          ClipRRect(
            borderRadius: BorderRadius.circular(14),
            child: Container(
              height: _mapHeight,
              decoration: const BoxDecoration(
                image: DecorationImage(
                  image: AssetImage('assets/adventure/node_background.png'),
                  fit: BoxFit.none,
                  repeat: ImageRepeat.repeat,
                  alignment: Alignment.topLeft,
                  scale: 2.6,
                ),
              ),
              child: SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: SizedBox(
                  width: mapWidth,
                  child: Stack(
                    children: [
                      Positioned.fill(
                        child: IgnorePointer(
                          child: CustomPaint(
                            painter: _MapPathPainter(
                              anchors: List.generate(
                                quizzes.length,
                                _connectorAnchorsForIndex,
                              ),
                              activeSegments: completedCount.clamp(
                                0,
                                quizzes.length > 1 ? quizzes.length - 1 : 0,
                              ),
                            ),
                          ),
                        ),
                      ),
                      for (int i = 0; i < quizzes.length; i++) ...[
                        _buildNode(i),
                      ],
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  _PathAnchors _connectorAnchorsForIndex(int index) {
    final left = _mapSidePadding + (index * _slotWidth);
    final top = _nodeTop(index);
    final centerX = left + (_slotWidth / 2);

    // Side anchors are intentionally calibrated to the house "door" zone.
    final out = Offset(centerX + (_nodeImageWidth * 0.32), top + 52);
    final incoming = Offset(centerX - (_nodeImageWidth * 0.32), top + 52);
    return _PathAnchors(incoming: incoming, outgoing: out);
  }

  Widget _buildNode(int i) {
    final quiz = quizzes[i];
    final state = progressByQuiz[quiz.id];
    final unlocked = isUnlocked(quiz);
    final nodeNumber = _quizOrderNumber(quiz);
    final nodeAsset = _nodeAssetFor(state: state, unlocked: unlocked);
    final titleTop = _titleTopFor(i, nodeAsset);
    final status = unlocked ? (state?.status ?? 'not_started') : 'locked';

    final left = _mapSidePadding + (i * _slotWidth);
    final top = _nodeTop(i);

    return Positioned(
      left: left,
      top: top,
      child: SizedBox(
        width: _slotWidth,
        height: 126,
        child: Stack(
          clipBehavior: Clip.none,
          children: [
            Positioned(
              left: (_slotWidth - _nodeImageWidth) / 2,
              top: 0,
              child: GestureDetector(
                onTap: unlocked ? () => onNodeTap(quiz) : null,
                child: SizedBox(
                  width: _nodeImageWidth,
                  height: _nodeImageHeight,
                  child: _NodeSprite(
                    asset: nodeAsset,
                    status: status,
                    isChallenge: quiz.isChallenge,
                  ),
                ),
              ),
            ),
            Positioned(
              left: (_slotWidth - _nodeImageWidth) / 2 + 6,
              top: -2,
              child: _NodeNumberBadge(
                number: nodeNumber,
                locked: !unlocked,
                isChallenge: quiz.isChallenge,
              ),
            ),
            Positioned(
              top: titleTop,
              left: 0,
              right: 0,
              child: Center(
                child: SizedBox(
                  width: 86,
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 5,
                      vertical: 1.5,
                    ),
                    decoration: BoxDecoration(
                      color: const Color(0xECFFF7E6),
                      borderRadius: BorderRadius.circular(999),
                      border: Border.all(
                        color: const Color(0xFFD7C18F),
                        width: 0.9,
                      ),
                      boxShadow: const [
                        BoxShadow(
                          color: Color(0x29000000),
                          blurRadius: 3,
                          offset: Offset(0, 1),
                        ),
                      ],
                    ),
                    child: Text(
                      quiz.title,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontSize: 10.5,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF1F2937),
                        letterSpacing: 0.05,
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _nodeAssetFor({
    required QuizProgressState? state,
    required bool unlocked,
  }) {
    if (!unlocked) return 'assets/adventure/node_locked.png';

    final stars = state?.stars ?? 0;
    final everCompleted = state?.everCompleted ?? false;

    // Keep best-score house visible during retakes.
    if (everCompleted) {
      if (stars >= 3) return 'assets/adventure/node_3stars.png';
      if (stars == 2) return 'assets/adventure/node_2stars.png';
      if (stars == 1) return 'assets/adventure/node_1star.png';
      return 'assets/adventure/node_0stars.png';
    }

    // Unlocked but never completed uses the dedicated unlocked house model.
    return 'assets/adventure/node_unlocked.png';
  }

  double _titleTopFor(int index, String asset) {
    // Local stack coordinates only: node depth is already handled by _nodeTop.
    // Locked/unlocked house sprites have extra transparent lower area, so their
    // labels need to sit slightly higher to match starred house alignment.
    if (asset.contains('node_locked') || asset.contains('node_unlocked')) {
      return _nodeImageHeight - (index.isEven ? 14 : 8);
    }
    return _nodeImageHeight + 1;
  }

  double _nodeTop(int index) => index.isEven ? 8 : 44;

  int _quizOrderNumber(QuizItem quiz) {
    final index = allQuizzes.indexWhere((item) => item.id == quiz.id);
    return index >= 0 ? index + 1 : 0;
  }
}

class _PathAnchors {
  const _PathAnchors({required this.incoming, required this.outgoing});

  final Offset incoming;
  final Offset outgoing;
}

class _MapPathPainter extends CustomPainter {
  const _MapPathPainter({required this.anchors, required this.activeSegments});

  final List<_PathAnchors> anchors;
  final int activeSegments;

  @override
  void paint(Canvas canvas, Size size) {
    if (anchors.length < 2) return;

    final edgePaint =
        Paint()
          ..color = const Color(0x80B6924D)
          ..style = PaintingStyle.stroke
          ..strokeWidth = 11
          ..strokeCap = StrokeCap.round;

    final basePaint =
        Paint()
          ..color = const Color(0xFFECD9A8)
          ..style = PaintingStyle.stroke
          ..strokeWidth = 9
          ..strokeCap = StrokeCap.round;

    final innerPaint =
        Paint()
          ..color = const Color(0xFFF7EBC8)
          ..style = PaintingStyle.stroke
          ..strokeWidth = 5.5
          ..strokeCap = StrokeCap.round;

    for (int i = 0; i < anchors.length - 1; i++) {
      final start = anchors[i].outgoing;
      final end = anchors[i + 1].incoming;
      final midX = (start.dx + end.dx) / 2;
      final curveLift = (i.isEven ? -14.0 : 14.0);
      final isActive = i < activeSegments;

      final path =
          Path()
            ..moveTo(start.dx, start.dy)
            ..cubicTo(
              midX - 18,
              start.dy + curveLift,
              midX + 18,
              end.dy - curveLift,
              end.dx,
              end.dy,
            );

      canvas.drawPath(path, edgePaint);
      canvas.drawPath(
        path,
        isActive
            ? (Paint()
              ..color = const Color(0xFFE8C97E)
              ..style = PaintingStyle.stroke
              ..strokeWidth = 9
              ..strokeCap = StrokeCap.round)
            : basePaint,
      );
      canvas.drawPath(
        path,
        isActive
            ? (Paint()
              ..color = const Color(0xFFFFF2CC)
              ..style = PaintingStyle.stroke
              ..strokeWidth = 5.5
              ..strokeCap = StrokeCap.round)
            : innerPaint,
      );
    }
  }

  @override
  bool shouldRepaint(covariant _MapPathPainter oldDelegate) {
    if (oldDelegate.anchors.length != anchors.length ||
        oldDelegate.activeSegments != activeSegments) {
      return true;
    }
    for (int i = 0; i < anchors.length; i++) {
      if (oldDelegate.anchors[i].incoming != anchors[i].incoming ||
          oldDelegate.anchors[i].outgoing != anchors[i].outgoing) {
        return true;
      }
    }
    return false;
  }
}

class _NodeNumberBadge extends StatelessWidget {
  const _NodeNumberBadge({
    required this.number,
    required this.locked,
    required this.isChallenge,
  });

  final int number;
  final bool locked;
  final bool isChallenge;

  @override
  Widget build(BuildContext context) {
    return Container(
      constraints: const BoxConstraints(minWidth: 24),
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: locked ? const Color(0xFF334155) : const Color(0xFF0F766E),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: Colors.white, width: 1.4),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            '$number',
            textAlign: TextAlign.center,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 11,
              fontWeight: FontWeight.w800,
            ),
          ),
          if (isChallenge) ...[
            const SizedBox(width: 3),
            const Icon(
              Icons.emoji_events_rounded,
              size: 11,
              color: Colors.amber,
            ),
          ],
        ],
      ),
    );
  }
}

class _NodeSprite extends StatefulWidget {
  const _NodeSprite({
    required this.asset,
    required this.status,
    required this.isChallenge,
  });

  final String asset;
  final String status;
  final bool isChallenge;

  @override
  State<_NodeSprite> createState() => _NodeSpriteState();
}

class _NodeSpriteState extends State<_NodeSprite>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1300),
    )..repeat(reverse: true);
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final shouldPulse =
        widget.status == 'in_progress' || widget.status == 'not_started';
    final pulseStrength = widget.status == 'in_progress' ? 0.04 : 0.02;
    final challengeBoost = widget.isChallenge ? 0.01 : 0;

    return AnimatedBuilder(
      animation: _controller,
      builder: (context, child) {
        final t = _controller.value;
        final delta = shouldPulse ? ((t - 0.5).abs() * 2) : 0;
        final scale = 1 - ((delta - 0.5) * (pulseStrength + challengeBoost));
        return Transform.scale(scale: scale, child: child);
      },
      child: Image.asset(widget.asset, fit: BoxFit.contain),
    );
  }
}
