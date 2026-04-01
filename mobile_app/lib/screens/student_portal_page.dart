import 'package:flutter/material.dart';
import 'package:mobile_app/models/quiz_item.dart';
import 'package:mobile_app/models/quiz_progress_state.dart';
import 'package:mobile_app/screens/question_flow_page.dart';
import 'package:mobile_app/services/api_service.dart';
import 'package:mobile_app/services/progress_storage_service.dart';
import 'package:mobile_app/widgets/adventure_map.dart';
import 'package:mobile_app/widgets/quiz_card.dart';

class StudentPortalPage extends StatefulWidget {
  const StudentPortalPage({super.key});

  @override
  State<StudentPortalPage> createState() => _StudentPortalPageState();
}

class _StudentPortalPageState extends State<StudentPortalPage> {
  final TextEditingController searchController = TextEditingController();
  final ApiService apiService = ApiService();
  final ProgressStorageService progressStorageService =
      ProgressStorageService();

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
    await _loadProgressFromLocal();
    if (mounted) {
      setState(() {});
    }
  }

  Future<void> login() async {
    setState(() {
      loading = true;
      status = 'Logging in...';
    });

    try {
      token = await apiService.login(
        email: 'test@example.com',
        password: 'password',
      );
      status = 'Connected';
    } catch (e) {
      status = 'Login error: $e';
    }
  }

  Future<void> loadQuizzes() async {
    if (token == null) {
      setState(() => loading = false);
      return;
    }

    try {
      final loaded = await apiService.fetchQuizzes(token: token!);
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

  Future<void> _loadProgressFromLocal() async {
    try {
      final loaded = await progressStorageService.load(quizzes: quizzes);
      loaded.forEach((quizId, state) {
        progressByQuiz[quizId] = state;
      });
    } catch (_) {
      // Keep app functional even if local progress parsing fails.
    }
  }

  Future<void> _saveProgressToLocal() async {
    try {
      await progressStorageService.save(progressByQuiz);
    } catch (_) {
      // Ignore local save errors in this prototype stage.
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
      final textMatches =
          search.isEmpty ||
          quiz.title.toLowerCase().contains(search) ||
          quiz.subject.toLowerCase().contains(search);
      return gradeMatches && textMatches;
    }).toList();
  }

  bool isQuizUnlocked(QuizItem quiz) {
    final all = quizzes;
    final index = all.indexWhere((item) => item.id == quiz.id);
    if (index <= 0) return true;
    final previousQuiz = all[index - 1];
    return progressByQuiz[previousQuiz.id]?.completed ?? false;
  }

  Future<void> startQuiz(QuizItem quiz) async {
    final state = progressByQuiz[quiz.id];
    if (state == null || token == null) return;

    int? targetQuestionId = state.currentQuestionId;
    if (targetQuestionId == null) {
      try {
        targetQuestionId = await apiService.startQuiz(
          token: token!,
          quizId: quiz.id,
        );
      } catch (e) {
        if (!mounted) return;
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Start error: $e')));
        return;
      }
    }

    if (targetQuestionId == null || !mounted) return;
    await Navigator.of(context).push(
      MaterialPageRoute(
        builder:
            (_) => QuestionFlowPage(
              apiService: apiService,
              token: token!,
              quiz: quiz,
              initialQuestionId: targetQuestionId!,
              state: state,
              onProgressChanged: _saveProgressToLocal,
            ),
      ),
    );

    await _saveProgressToLocal();
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
        centerTitle: false,
        elevation: 0,
        scrolledUnderElevation: 0,
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
        child:
            loading
                ? const Center(child: CircularProgressIndicator())
                : SafeArea(
                  child: Padding(
                    padding: const EdgeInsets.fromLTRB(14, 10, 14, 14),
                    child: Column(
                      children: [
                        if (status != 'Ready')
                          Padding(
                            padding: const EdgeInsets.only(bottom: 8),
                            child: Text(
                              status,
                              style: const TextStyle(fontSize: 13),
                            ),
                          ),
                        AdventureMap(
                          quizzes: visibleQuizzes,
                          allQuizzes: quizzes,
                          progressByQuiz: progressByQuiz,
                          isUnlocked: (quiz) => isQuizUnlocked(quiz),
                          onNodeTap: (quiz) => startQuiz(quiz),
                        ),
                        const SizedBox(height: 10),
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: const Color(0xFFDCE5F2)),
                            boxShadow: const [
                              BoxShadow(
                                color: Color(0x10111A2B),
                                blurRadius: 10,
                                offset: Offset(0, 3),
                              ),
                            ],
                          ),
                          child: LayoutBuilder(
                            builder: (context, constraints) {
                              final stackVertically =
                                  constraints.maxWidth < 360;
                              if (stackVertically) {
                                return Column(
                                  children: [
                                    TextField(
                                      controller: searchController,
                                      onChanged: (_) => setState(() {}),
                                      decoration: const InputDecoration(
                                        hintText:
                                            'Search by quiz title or subject...',
                                        border: OutlineInputBorder(),
                                        isDense: true,
                                      ),
                                    ),
                                    const SizedBox(height: 8),
                                    Align(
                                      alignment: Alignment.centerRight,
                                      child: DropdownButton<String>(
                                        value: selectedGrade,
                                        onChanged: (value) {
                                          if (value == null) return;
                                          setState(() => selectedGrade = value);
                                        },
                                        items:
                                            gradeOptions
                                                .map(
                                                  (grade) => DropdownMenuItem(
                                                    value: grade,
                                                    child: Text(grade),
                                                  ),
                                                )
                                                .toList(),
                                      ),
                                    ),
                                  ],
                                );
                              }

                              return Row(
                                children: [
                                  Expanded(
                                    child: TextField(
                                      controller: searchController,
                                      onChanged: (_) => setState(() {}),
                                      decoration: const InputDecoration(
                                        hintText:
                                            'Search by quiz title or subject...',
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
                                    items:
                                        gradeOptions
                                            .map(
                                              (grade) => DropdownMenuItem(
                                                value: grade,
                                                child: Text(grade),
                                              ),
                                            )
                                            .toList(),
                                  ),
                                ],
                              );
                            },
                          ),
                        ),
                        const SizedBox(height: 8),
                        Expanded(
                          child:
                              visibleQuizzes.isEmpty
                                  ? const Center(
                                    child: Text(
                                      'No quizzes found for this filter.',
                                    ),
                                  )
                                  : ListView.builder(
                                    itemCount: visibleQuizzes.length,
                                    itemBuilder: (context, index) {
                                      final quiz = visibleQuizzes[index];
                                      final progress = progressByQuiz[quiz.id]!;
                                      final unlocked = isQuizUnlocked(quiz);

                                      return QuizCard(
                                        quiz: quiz,
                                        progress: progress,
                                        unlocked: unlocked,
                                        onStart:
                                            unlocked
                                                ? () => startQuiz(quiz)
                                                : null,
                                      );
                                    },
                                  ),
                        ),
                      ],
                    ),
                  ),
                ),
      ),
    );
  }
}
