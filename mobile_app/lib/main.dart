import 'dart:convert';
import 'dart:math' as math;

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

void main() {
  runApp(const EduAppMobile());
}

class EduAppMobile extends StatelessWidget {
  const EduAppMobile({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'EduApp Student',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        fontFamily: 'Poppins',
        colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF0F766E)),
      ),
      home: const StudentPortalPage(),
    );
  }
}

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

class ChoiceItem {
  ChoiceItem({required this.id, required this.text});

  final int id;
  final String text;

  factory ChoiceItem.fromJson(Map<String, dynamic> json) {
    return ChoiceItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      text: (json['choice_text'] ?? '').toString(),
    );
  }
}

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
      choices: ((question['choices'] as List?) ?? [])
          .map((item) => ChoiceItem.fromJson(item as Map<String, dynamic>))
          .toList(),
      currentIndex: (progress['current_index'] as num?)?.toInt() ?? 0,
      totalQuestions: (progress['total_questions'] as num?)?.toInt() ?? 0,
      percent: (progress['percent'] as num?)?.toInt() ?? 0,
    );
  }
}

class QuizProgressState {
  QuizProgressState({required this.totalQuestions});

  final int totalQuestions;
  final Set<int> answeredQuestionIds = {};
  final Set<int> correctQuestionIds = {};
  int? currentQuestionId;
  bool completed = false;

  int get answeredCount => math.min(answeredQuestionIds.length, totalQuestions);
  int get correctCount => math.min(correctQuestionIds.length, totalQuestions);
  int get percent => totalQuestions == 0
      ? 0
      : ((answeredCount / totalQuestions) * 100).round();
  int get scorePercent => totalQuestions == 0
      ? 0
      : ((correctCount / totalQuestions) * 100).round();

  int get stars {
    if (!completed) return 0;
    if (scorePercent >= 90) return 3;
    if (scorePercent >= 70) return 2;
    if (scorePercent >= 50) return 1;
    return 0;
  }

  String get status {
    if (completed) return 'completed';
    if (answeredQuestionIds.isNotEmpty) return 'in_progress';
    return 'not_started';
  }
}

class StudentPortalPage extends StatefulWidget {
  const StudentPortalPage({super.key});

  @override
  State<StudentPortalPage> createState() => _StudentPortalPageState();
}

class _StudentPortalPageState extends State<StudentPortalPage> {
  static const String baseUrl = 'http://127.0.0.1:8000';
  final TextEditingController searchController = TextEditingController();

  String? token;
  String status = 'Connecting...';
  bool loading = true;

  List<QuizItem> quizzes = [];
  final Map<int, QuizProgressState> progressByQuiz = {};
  String selectedGrade = 'All levels';

  @override
  void initState() {
    super.initState();
    bootstrap();
  }

  Future<void> bootstrap() async {
    await login();
    await loadQuizzes();
  }

  Future<void> login() async {
    setState(() {
      loading = true;
      status = 'Logging in...';
    });

    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/v1/login'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'email': 'test@example.com',
          'password': 'password',
        }),
      );

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['token'] != null) {
        token = data['token'].toString();
        status = 'Connected';
      } else {
        status = data['message'] ?? 'Login failed (HTTP ${response.statusCode})';
      }
    } catch (e) {
      status = 'Login error: $e';
    }
  }

  Future<void> loadQuizzes() async {
    if (token == null) {
      setState(() {
        loading = false;
      });
      return;
    }

    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/v1/quizzes'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['data'] is List) {
        final loaded = (data['data'] as List)
            .map((item) => QuizItem.fromJson(item as Map<String, dynamic>))
            .toList();

        for (final quiz in loaded) {
          progressByQuiz.putIfAbsent(
            quiz.id,
            () => QuizProgressState(totalQuestions: quiz.questionCount),
          );
        }

        setState(() {
          quizzes = loaded;
          status = 'Ready';
        });
      } else {
        setState(() {
          status = data['message'] ?? 'Could not load quizzes';
        });
      }
    } catch (e) {
      setState(() {
        status = 'Quiz load error: $e';
      });
    } finally {
      setState(() {
        loading = false;
      });
    }
  }

  List<String> get gradeOptions {
    final grades = quizzes.map((q) => q.grade).toSet().toList()..sort();
    return ['All levels', ...grades];
  }

  List<QuizItem> get filteredQuizzes {
    final search = searchController.text.trim().toLowerCase();
    return quizzes.where((quiz) {
      final gradeMatches =
          selectedGrade == 'All levels' || quiz.grade == selectedGrade;
      final textMatches = search.isEmpty ||
          quiz.title.toLowerCase().contains(search) ||
          quiz.subject.toLowerCase().contains(search);
      return gradeMatches && textMatches;
    }).toList();
  }

  bool isUnlocked(List<QuizItem> list, int index) {
    if (index == 0) return true;
    final previousQuiz = list[index - 1];
    return progressByQuiz[previousQuiz.id]?.completed ?? false;
  }

  Future<void> startQuiz(QuizItem quiz) async {
    final state = progressByQuiz[quiz.id];
    final resumeQuestionId = state?.currentQuestionId;
    int? targetQuestionId = resumeQuestionId;

    if (targetQuestionId == null) {
      try {
        final response = await http.get(
          Uri.parse('$baseUrl/api/v1/quizzes/${quiz.id}/start'),
          headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer $token',
          },
        );
        final data = jsonDecode(response.body);

        if (response.statusCode == 200) {
          targetQuestionId =
              (data['data']['first_question_id'] as num?)?.toInt();
        } else {
          if (!mounted) return;
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(data['message']?.toString() ?? 'Could not start quiz'),
            ),
          );
          return;
        }
      } catch (e) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Start error: $e')),
        );
        return;
      }
    }

    if (targetQuestionId == null) return;

    if (!mounted) return;
    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => QuestionFlowPage(
          baseUrl: baseUrl,
          token: token!,
          quiz: quiz,
          initialQuestionId: targetQuestionId!,
          state: state!,
        ),
      ),
    );

    setState(() {});
  }

  @override
  void dispose() {
    searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final visibleQuizzes = filteredQuizzes;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Student Portal'),
        actions: [
          IconButton(
            onPressed: loading ? null : loadQuizzes,
            icon: const Icon(Icons.refresh),
          ),
        ],
      ),
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFFE8F1FF), Color(0xFFE1FAF2)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        child: loading
            ? const Center(child: CircularProgressIndicator())
            : Padding(
                padding: const EdgeInsets.all(14),
                child: Column(
                  children: [
                    if (status != 'Ready')
                      Padding(
                        padding: const EdgeInsets.only(bottom: 8),
                        child: Text(status, style: const TextStyle(fontSize: 13)),
                      ),
                    AdventureMap(
                      quizzes: visibleQuizzes,
                      progressByQuiz: progressByQuiz,
                      isUnlocked: (index) => isUnlocked(visibleQuizzes, index),
                      onNodeTap: (quiz) => startQuiz(quiz),
                    ),
                    const SizedBox(height: 10),
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: const Color(0xFFDCE5F2)),
                      ),
                      child: Row(
                        children: [
                          Expanded(
                            child: TextField(
                              controller: searchController,
                              onChanged: (_) => setState(() {}),
                              decoration: const InputDecoration(
                                hintText: 'Search by quiz title or subject...',
                                border: OutlineInputBorder(),
                                isDense: true,
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          DropdownButton<String>(
                            value: selectedGrade,
                            onChanged: (value) {
                              if (value == null) return;
                              setState(() => selectedGrade = value);
                            },
                            items: gradeOptions
                                .map(
                                  (grade) => DropdownMenuItem(
                                    value: grade,
                                    child: Text(grade),
                                  ),
                                )
                                .toList(),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 8),
                    Expanded(
                      child: visibleQuizzes.isEmpty
                          ? const Center(
                              child: Text('No quizzes found for this filter.'),
                            )
                          : ListView.builder(
                              itemCount: visibleQuizzes.length,
                              itemBuilder: (context, index) {
                                final quiz = visibleQuizzes[index];
                                final progress = progressByQuiz[quiz.id]!;
                                final unlocked = isUnlocked(visibleQuizzes, index);

                                return _QuizCard(
                                  quiz: quiz,
                                  progress: progress,
                                  unlocked: unlocked,
                                  onStart: unlocked ? () => startQuiz(quiz) : null,
                                );
                              },
                            ),
                    ),
                  ],
                ),
              ),
      ),
    );
  }
}

class _QuizCard extends StatelessWidget {
  const _QuizCard({
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
    final badge = status == 'completed'
        ? const _Badge(
            label: 'Completed',
            bg: Color(0xFFDCFCE7),
            border: Color(0xFF86EFAC),
            text: Color(0xFF166534),
          )
        : status == 'in_progress'
            ? const _Badge(
                label: 'In Progress',
                bg: Color(0xFFFFEDD5),
                border: Color(0xFFFDBA74),
                text: Color(0xFF9A3412),
              )
            : const _Badge(
                label: 'Not Started',
                bg: Color(0xFFDBEAFE),
                border: Color(0xFF93C5FD),
                text: Color(0xFF1E3A8A),
              );

    return Opacity(
      opacity: unlocked ? 1 : 0.7,
      child: Card(
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
                style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 16),
              ),
              const SizedBox(height: 4),
              Text(
                '${quiz.subject} • ${quiz.grade} • ${quiz.questionCount} questions',
                style: const TextStyle(color: Color(0xFF6B7280), fontSize: 13),
              ),
              const SizedBox(height: 8),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  badge,
                  _StarRow(stars: progress.stars),
                ],
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
                    backgroundColor: unlocked
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

class _Badge extends StatelessWidget {
  const _Badge({
    required this.label,
    required this.bg,
    required this.border,
    required this.text,
  });

  final String label;
  final Color bg;
  final Color border;
  final Color text;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: border),
      ),
      child: Text(
        label,
        style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: text),
      ),
    );
  }
}

class _StarRow extends StatelessWidget {
  const _StarRow({required this.stars});

  final int stars;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: List.generate(3, (index) {
        final on = index < stars;
        return Icon(
          Icons.star,
          size: 15,
          color: on ? const Color(0xFFF59E0B) : const Color(0xFFCBD5E1),
        );
      }),
    );
  }
}

class AdventureMap extends StatelessWidget {
  const AdventureMap({
    super.key,
    required this.quizzes,
    required this.progressByQuiz,
    required this.isUnlocked,
    required this.onNodeTap,
  });

  final List<QuizItem> quizzes;
  final Map<int, QuizProgressState> progressByQuiz;
  final bool Function(int index) isUnlocked;
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
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Adventure Path',
            style: TextStyle(fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 8),
          SizedBox(
            height: 136,
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
                              number: i + 1,
                              topOffset: i.isEven ? 4 : 34,
                              stars: progressByQuiz[quizzes[i].id]?.stars ?? 0,
                              status:
                                  progressByQuiz[quizzes[i].id]?.status ?? 'not_started',
                              unlocked: isUnlocked(i),
                              title: quizzes[i].title,
                              onTap: isUnlocked(i) ? () => onNodeTap(quizzes[i]) : null,
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
}

class _MapLinesPainter extends CustomPainter {
  _MapLinesPainter({required this.nodeCount});

  final int nodeCount;

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = const Color(0xFFCBD5E1)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 5
      ..strokeCap = StrokeCap.round;

    for (int i = 0; i < nodeCount - 1; i++) {
      final x1 = 54.0 + (i * 108.0);
      final x2 = 54.0 + ((i + 1) * 108.0);
      final y1 = i.isEven ? 36.0 : 66.0;
      final y2 = (i + 1).isEven ? 36.0 : 66.0;
      final path = Path()
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

    return Align(
      alignment: Alignment.topCenter,
      child: Padding(
        padding: EdgeInsets.only(top: topOffset),
        child: Column(
          children: [
            GestureDetector(
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
                child: Text(
                  unlocked ? '$number' : '🔒',
                  style: TextStyle(fontWeight: FontWeight.w800, color: text, fontSize: 18),
                ),
              ),
            ),
            const SizedBox(height: 3),
            Text(
              title,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700),
            ),
            _StarRow(stars: stars),
          ],
        ),
      ),
    );
  }
}

class QuestionFlowPage extends StatefulWidget {
  const QuestionFlowPage({
    super.key,
    required this.baseUrl,
    required this.token,
    required this.quiz,
    required this.initialQuestionId,
    required this.state,
  });

  final String baseUrl;
  final String token;
  final QuizItem quiz;
  final int initialQuestionId;
  final QuizProgressState state;

  @override
  State<QuestionFlowPage> createState() => _QuestionFlowPageState();
}

class _QuestionFlowPageState extends State<QuestionFlowPage> {
  QuestionPayload? question;
  bool loading = true;
  String? error;
  int? selectedChoiceId;
  bool answered = false;
  bool? result;
  int? correctChoiceId;
  int? nextQuestionId;
  bool finished = false;

  @override
  void initState() {
    super.initState();
    _loadQuestion(widget.state.currentQuestionId ?? widget.initialQuestionId);
  }

  Future<void> _loadQuestion(int questionId) async {
    setState(() {
      loading = true;
      error = null;
      selectedChoiceId = null;
      answered = false;
      result = null;
      correctChoiceId = null;
      nextQuestionId = null;
      finished = false;
    });

    try {
      final response = await http.get(
        Uri.parse('${widget.baseUrl}/api/v1/quizzes/${widget.quiz.id}/questions/$questionId'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer ${widget.token}',
        },
      );

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['data'] != null) {
        final payload = QuestionPayload.fromApi(data['data'] as Map<String, dynamic>);
        widget.state.currentQuestionId = payload.questionId;
        widget.state.completed = false;
        widget.state.currentQuestionId = payload.questionId;

        setState(() {
          question = payload;
          loading = false;
        });
      } else {
        setState(() {
          error = data['message']?.toString() ?? 'Could not load question.';
          loading = false;
        });
      }
    } catch (e) {
      setState(() {
        error = 'Question load error: $e';
        loading = false;
      });
    }
  }

  Future<void> _submitAnswer() async {
    final current = question;
    if (current == null || selectedChoiceId == null || answered) return;

    try {
      final response = await http.post(
        Uri.parse('${widget.baseUrl}/api/v1/questions/${current.questionId}/answer'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer ${widget.token}',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({'choice_id': selectedChoiceId}),
      );

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['data'] != null) {
        final payload = data['data'] as Map<String, dynamic>;
        final isCorrect = (payload['is_correct'] as bool?) ?? false;
        final correctId = (payload['correct_choice_id'] as num?)?.toInt();
        final nextId = (payload['next_question_id'] as num?)?.toInt();
        final isFinished = (payload['finished'] as bool?) ?? false;

        widget.state.answeredQuestionIds.add(current.questionId);
        if (isCorrect) {
          widget.state.correctQuestionIds.add(current.questionId);
        }
        widget.state.currentQuestionId = nextId;
        widget.state.completed = isFinished;

        setState(() {
          answered = true;
          result = isCorrect;
          correctChoiceId = correctId;
          nextQuestionId = nextId;
          finished = isFinished;
        });
      } else {
        setState(() {
          error = data['message']?.toString() ?? 'Could not submit answer.';
        });
      }
    } catch (e) {
      setState(() {
        error = 'Submit error: $e';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(widget.quiz.title)),
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFFEEF6FF), Color(0xFFF4F7FB)],
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
          ),
        ),
        child: loading
            ? const Center(child: CircularProgressIndicator())
            : error != null
                ? Center(child: Text(error!))
                : _buildQuestionCard(),
      ),
    );
  }

  Widget _buildQuestionCard() {
    final q = question!;
    return Center(
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 430),
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(14),
          child: Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: const Color(0xFFE5E7EB)),
              boxShadow: const [
                BoxShadow(
                  color: Color(0x14111A2B),
                  blurRadius: 16,
                  offset: Offset(0, 6),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Question ${q.currentIndex} of ${q.totalQuestions}',
                  style: const TextStyle(
                    fontSize: 13,
                    color: Color(0xFF6B7280),
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 8),
                ClipRRect(
                  borderRadius: BorderRadius.circular(999),
                  child: LinearProgressIndicator(
                    value: q.percent / 100,
                    minHeight: 8,
                    color: const Color(0xFF14B8A6),
                    backgroundColor: const Color(0xFFE5E7EB),
                  ),
                ),
                const SizedBox(height: 14),
                Text(
                  q.questionText,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w600,
                    height: 1.3,
                  ),
                ),
                if ((q.imagePath ?? '').trim().isNotEmpty) ...[
                  const SizedBox(height: 10),
                  ClipRRect(
                    borderRadius: BorderRadius.circular(12),
                    child: Image.network(
                      _resolveImageUrl(q.imagePath!),
                      height: 180,
                      width: double.infinity,
                      fit: BoxFit.cover,
                      errorBuilder: (context, _, __) => const SizedBox.shrink(),
                    ),
                  ),
                ],
                const SizedBox(height: 12),
                for (final choice in q.choices) _choiceTile(choice),
                if (!answered)
                  SizedBox(
                    width: double.infinity,
                    child: FilledButton(
                      onPressed: selectedChoiceId == null ? null : _submitAnswer,
                      child: const Text('Submit Answer'),
                    ),
                  ),
                if (answered) ...[
                  const SizedBox(height: 10),
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: (result ?? false)
                          ? const Color(0xFFDCFCE7)
                          : const Color(0xFFFEE2E2),
                      border: Border.all(
                        color: (result ?? false)
                            ? const Color(0xFF86EFAC)
                            : const Color(0xFFFCA5A5),
                      ),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      (result ?? false)
                          ? 'Nice work, that is correct.'
                          : 'Not quite, but good try.',
                      style: TextStyle(
                        fontWeight: FontWeight.w700,
                        color: (result ?? false)
                            ? const Color(0xFF166534)
                            : const Color(0xFF991B1B),
                      ),
                    ),
                  ),
                  const SizedBox(height: 10),
                  SizedBox(
                    width: double.infinity,
                    child: FilledButton(
                      onPressed: () {
                        if (finished || nextQuestionId == null) {
                          Navigator.of(context).pop();
                        } else {
                          _loadQuestion(nextQuestionId!);
                        }
                      },
                      style: FilledButton.styleFrom(
                        backgroundColor: const Color(0xFF0F766E),
                      ),
                      child: Text(
                        finished ? 'Back to Quiz Selection' : 'Continue to Next Question',
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _choiceTile(ChoiceItem choice) {
    final isSelected = selectedChoiceId == choice.id;
    final isCorrect = correctChoiceId == choice.id;
    final showCorrect = answered && isCorrect;
    final showWrong = answered && isSelected && !isCorrect;

    Color border = const Color(0xFFE5E7EB);
    Color bg = Colors.white;
    if (showCorrect) {
      border = const Color(0xFF16A34A);
      bg = const Color(0xFFDCFCE7);
    } else if (showWrong) {
      border = const Color(0xFFDC2626);
      bg = const Color(0xFFFEE2E2);
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: border),
      ),
      child: RadioListTile<int>(
        value: choice.id,
        groupValue: selectedChoiceId,
        onChanged: answered
            ? null
            : (value) {
                if (value == null) return;
                setState(() => selectedChoiceId = value);
              },
        title: Text(choice.text),
        dense: true,
        contentPadding: const EdgeInsets.symmetric(horizontal: 10),
      ),
    );
  }

  String _resolveImageUrl(String raw) {
    final trimmed = raw.trim();
    if (trimmed.startsWith('http://') || trimmed.startsWith('https://')) {
      return trimmed;
    }
    if (trimmed.startsWith('/')) {
      return '${widget.baseUrl}$trimmed';
    }
    return '${widget.baseUrl}/$trimmed';
  }
}
