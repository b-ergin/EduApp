import 'package:flutter/material.dart';
import 'package:mobile_app/models/quiz_item.dart';
import 'package:mobile_app/models/quiz_progress_state.dart';

class QuizResultPage extends StatefulWidget {
  const QuizResultPage({super.key, required this.quiz, required this.state});

  final QuizItem quiz;
  final QuizProgressState state;

  @override
  State<QuizResultPage> createState() => _QuizResultPageState();
}

class _QuizResultPageState extends State<QuizResultPage>
    with SingleTickerProviderStateMixin {
  static const String _fuzzyCelebrate = 'assets/mascot/fuzzy_celebrate.png';
  late final AnimationController _controller;
  late final Animation<double> _scale;
  late final Animation<double> _float;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1200),
    )..repeat(reverse: true);

    _scale = Tween<double>(
      begin: 0.96,
      end: 1.03,
    ).animate(CurvedAnimation(parent: _controller, curve: Curves.easeInOut));
    _float = Tween<double>(
      begin: -4,
      end: 4,
    ).animate(CurvedAnimation(parent: _controller, curve: Curves.easeInOut));
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final percent =
        widget.state.totalQuestions == 0
            ? 0
            : ((widget.state.correctCount / widget.state.totalQuestions) * 100)
                .round();
    final attemptStars = _starsFromPercent(percent);
    final earnedXp = (widget.state.correctCount * 10) + 20;

    return Scaffold(
      appBar: AppBar(title: const Text('Quiz Results')),
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFFEEF6FF), Color(0xFFF4F7FB)],
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
          ),
        ),
        child: SafeArea(
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 430),
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(14),
                child: Container(
                  padding: const EdgeInsets.all(18),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: const Color(0xFFE5E7EB)),
                    boxShadow: const [
                      BoxShadow(
                        color: Color(0x14111A2B),
                        blurRadius: 18,
                        offset: Offset(0, 8),
                      ),
                    ],
                  ),
                  child: Column(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 14,
                          vertical: 9,
                        ),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFAFDFF),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: const Color(0xFFBAE6FD)),
                        ),
                        child: const Text(
                          'Awesome! You finished this challenge!',
                          textAlign: TextAlign.center,
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w800,
                            color: Color(0xFF0F172A),
                          ),
                        ),
                      ),
                      const SizedBox(height: 8),
                      AnimatedBuilder(
                        animation: _controller,
                        builder: (context, child) {
                          return Transform.translate(
                            offset: Offset(0, _float.value),
                            child: Transform.scale(
                              scale: _scale.value,
                              child: child,
                            ),
                          );
                        },
                        child: Image.asset(
                          _fuzzyCelebrate,
                          height: 220,
                          fit: BoxFit.contain,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        widget.quiz.title,
                        style: const TextStyle(
                          fontSize: 22,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      const SizedBox(height: 10),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: List.generate(3, (index) {
                          final threshold = (index + 1) / 3;
                          final reveal = percent / 100 >= threshold;
                          return AnimatedScale(
                            duration: Duration(
                              milliseconds: 350 + (index * 120),
                            ),
                            scale: reveal ? 1 : 0.8,
                            child: Icon(
                              index < attemptStars
                                  ? Icons.star_rounded
                                  : Icons.star_border_rounded,
                              size: 40,
                              color:
                                  index < attemptStars
                                      ? const Color(0xFFF59E0B)
                                      : const Color(0xFFCBD5E1),
                            ),
                          );
                        }),
                      ),
                      const SizedBox(height: 10),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        alignment: WrapAlignment.center,
                        children: [
                          _metricChip(
                            icon: Icons.task_alt_rounded,
                            text:
                                '${widget.state.correctCount}/${widget.state.totalQuestions} correct',
                          ),
                          _metricChip(
                            icon: Icons.bolt_rounded,
                            text: '+$earnedXp XP',
                          ),
                          _metricChip(
                            icon: Icons.trending_up_rounded,
                            text: 'Score $percent%',
                          ),
                        ],
                      ),
                      const SizedBox(height: 18),
                      SizedBox(
                        width: double.infinity,
                        child: FilledButton(
                          onPressed: () => Navigator.of(context).pop(),
                          style: FilledButton.styleFrom(
                            backgroundColor: const Color(0xFF0F766E),
                            foregroundColor: Colors.white,
                          ),
                          child: const Text('Back to Adventure Map'),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  int _starsFromPercent(int percent) {
    if (percent >= 90) return 3;
    if (percent >= 70) return 2;
    if (percent >= 50) return 1;
    return 0;
  }

  Widget _metricChip({required IconData icon, required String text}) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: const Color(0xFF0F766E)),
          const SizedBox(width: 4),
          Text(
            text,
            style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700),
          ),
        ],
      ),
    );
  }
}
