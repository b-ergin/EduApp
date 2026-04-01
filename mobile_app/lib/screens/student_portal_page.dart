import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
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
  String? selectedGrade;
  bool showListView = true;
  bool showSearchBar = false;

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
      loaded.sort((a, b) {
        final byOrder = a.sortOrder.compareTo(b.sortOrder);
        if (byOrder != 0) return byOrder;
        return a.id.compareTo(b.id);
      });
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

  List<String> get gradeLevels {
    final grades = quizzes.map((q) => q.grade).toSet().toList()..sort();
    return grades;
  }

  List<QuizItem> get filteredQuizzes {
    if (selectedGrade == null || !gradeLevels.contains(selectedGrade)) {
      return [];
    }

    final search = searchController.text.trim().toLowerCase();
    return quizzes.where((quiz) {
      final gradeMatches = quiz.grade == selectedGrade;
      final textMatches =
          search.isEmpty ||
          quiz.title.toLowerCase().contains(search) ||
          quiz.subject.toLowerCase().contains(search);
      return gradeMatches && textMatches;
    }).toList();
  }

  List<QuizItem> orderedQuizzesForMap() {
    if (selectedGrade == null || !gradeLevels.contains(selectedGrade)) {
      return [];
    }

    final byLevel =
        quizzes.where((quiz) => quiz.grade == selectedGrade).toList();

    byLevel.sort((a, b) {
      final byOrder = a.sortOrder.compareTo(b.sortOrder);
      if (byOrder != 0) return byOrder;
      return a.id.compareTo(b.id);
    });

    return byLevel;
  }

  bool isQuizUnlocked(QuizItem quiz, List<QuizItem> orderedQuizzes) {
    final index = orderedQuizzes.indexWhere((item) => item.id == quiz.id);
    if (index <= 0) return true;
    final previousQuiz = orderedQuizzes[index - 1];
    return progressByQuiz[previousQuiz.id]?.completed ?? false;
  }

  Future<void> startQuiz(QuizItem quiz) async {
    final state = progressByQuiz[quiz.id];
    if (state == null || token == null) return;

    // Completed quizzes should restart from a clean state (retake behavior).
    if (state.completed) {
      state.answeredQuestionIds.clear();
      state.correctQuestionIds.clear();
      state.currentQuestionId = null;
      state.completed = false;
    }

    int targetQuestionId = state.currentQuestionId ?? 0;
    if (targetQuestionId == 0) {
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

    if (!mounted) return;
    await Navigator.of(context).push(
      MaterialPageRoute(
        builder:
            (_) => QuestionFlowPage(
              apiService: apiService,
              token: token!,
              quiz: quiz,
              initialQuestionId: targetQuestionId,
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
    final hasSelectedLevel =
        selectedGrade != null && gradeLevels.contains(selectedGrade);
    final visibleQuizzes = hasSelectedLevel ? filteredQuizzes : <QuizItem>[];
    final mapQuizzes = hasSelectedLevel ? orderedQuizzesForMap() : <QuizItem>[];

    return Scaffold(
      appBar: AppBar(
        titleSpacing: 10,
        title: SizedBox(
          height: 52,
          child: Stack(
            alignment: Alignment.center,
            children: [
              Align(
                alignment: Alignment.centerLeft,
                child: _buildEqMark(size: 44),
              ),
              Transform.translate(
                offset: const Offset(68, 0),
                child: _buildWordmark(width: 220, height: 50),
              ),
            ],
          ),
        ),
        centerTitle: false,
        elevation: 0,
        scrolledUnderElevation: 0,
        actions: [
          IconButton(
            onPressed:
                hasSelectedLevel
                    ? () {
                      setState(() {
                        showSearchBar = !showSearchBar;
                        if (!showSearchBar) {
                          searchController.clear();
                        }
                      });
                    }
                    : null,
            icon: Icon(showSearchBar ? Icons.close : Icons.search),
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
                    child: RefreshIndicator(
                      onRefresh: () async {
                        await loadQuizzes();
                        await _loadProgressFromLocal();
                        if (mounted) {
                          setState(() {});
                        }
                      },
                      child: ListView(
                        physics: const AlwaysScrollableScrollPhysics(),
                        keyboardDismissBehavior:
                            ScrollViewKeyboardDismissBehavior.onDrag,
                        children: [
                          if (status != 'Ready')
                            Padding(
                              padding: const EdgeInsets.only(bottom: 8),
                              child: Text(
                                status,
                                style: const TextStyle(fontSize: 13),
                              ),
                            ),
                          if (!hasSelectedLevel)
                            _buildLevelPicker()
                          else ...[
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  'Level: $selectedGrade',
                                  style: const TextStyle(
                                    fontWeight: FontWeight.w700,
                                    color: Color(0xFF0F172A),
                                  ),
                                ),
                                TextButton.icon(
                                  onPressed: () {
                                    setState(() {
                                      selectedGrade = null;
                                      showListView = true;
                                      showSearchBar = false;
                                      searchController.clear();
                                    });
                                  },
                                  icon: const Icon(Icons.swap_horiz),
                                  label: const Text('Change Level'),
                                ),
                              ],
                            ),
                            if (showSearchBar) _buildSearchPanel(),
                            const SizedBox(height: 8),
                            AdventureMap(
                              quizzes: mapQuizzes,
                              allQuizzes: mapQuizzes,
                              progressByQuiz: progressByQuiz,
                              isUnlocked:
                                  (quiz) => isQuizUnlocked(quiz, mapQuizzes),
                              onNodeTap: (quiz) => startQuiz(quiz),
                            ),
                            const SizedBox(height: 2),
                            Align(
                              alignment: Alignment.centerRight,
                              child: TextButton.icon(
                                onPressed: () {
                                  setState(() => showListView = !showListView);
                                },
                                icon: Icon(
                                  showListView
                                      ? Icons.visibility_off
                                      : Icons.visibility,
                                ),
                                label: Text(
                                  showListView ? 'Hide List' : 'Show List',
                                ),
                              ),
                            ),
                            if (showListView) ...[
                              const SizedBox(height: 4),
                              if (visibleQuizzes.isEmpty)
                                const Center(
                                  child: Padding(
                                    padding: EdgeInsets.symmetric(vertical: 12),
                                    child: Text(
                                      'No quizzes found for this filter.',
                                    ),
                                  ),
                                )
                              else
                                ...visibleQuizzes.map((quiz) {
                                  final progress = progressByQuiz[quiz.id]!;
                                  final unlocked = isQuizUnlocked(
                                    quiz,
                                    mapQuizzes,
                                  );
                                  return QuizCard(
                                    quiz: quiz,
                                    progress: progress,
                                    unlocked: unlocked,
                                    onStart:
                                        unlocked ? () => startQuiz(quiz) : null,
                                  );
                                }),
                            ],
                          ],
                        ],
                      ),
                    ),
                  ),
                ),
      ),
    );
  }

  Widget _buildSearchPanel() {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(8),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFDCE5F2)),
      ),
      child: TextField(
        controller: searchController,
        autofocus: true,
        onChanged: (_) => setState(() {}),
        decoration: InputDecoration(
          hintText: 'Search quiz or subject...',
          prefixIcon: const Icon(Icons.search),
          border: const OutlineInputBorder(),
          isDense: true,
          suffixIcon:
              searchController.text.isEmpty
                  ? null
                  : IconButton(
                    onPressed: () {
                      setState(() {
                        searchController.clear();
                      });
                    },
                    icon: const Icon(Icons.clear),
                  ),
        ),
      ),
    );
  }

  Widget _buildLevelPicker() {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(12),
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
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Choose Your Level First',
            style: TextStyle(fontWeight: FontWeight.w800, fontSize: 15),
          ),
          const SizedBox(height: 6),
          const Text(
            'Pick a level to load its adventure path and quizzes.',
            style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
          ),
          const SizedBox(height: 10),
          if (gradeLevels.isEmpty)
            const Text(
              'No levels found yet.',
              style: TextStyle(fontSize: 13, color: Color(0xFF64748B)),
            )
          else
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children:
                  gradeLevels
                      .map(
                        (grade) => FilledButton.tonal(
                          onPressed: () {
                            setState(() {
                              selectedGrade = grade;
                              searchController.clear();
                              showSearchBar = false;
                              showListView = true;
                            });
                          },
                          child: Text(grade),
                        ),
                      )
                      .toList(),
            ),
        ],
      ),
    );
  }

  Widget _buildEqMark({double size = 56}) {
    return SizedBox(
      width: size,
      height: size,
      child: ClipRRect(
        borderRadius: BorderRadius.circular(12),
        child: SvgPicture.asset(
          'assets/branding/eq_logo.svg',
          fit: BoxFit.contain,
          height: size,
        ),
      ),
    );
  }

  Widget _buildWordmark({double width = 220, double height = 48}) {
    return SizedBox(
      width: width,
      height: height,
      child: SvgPicture.asset(
        'assets/branding/text_logo.svg',
        fit: BoxFit.contain,
        height: height,
      ),
    );
  }
}
