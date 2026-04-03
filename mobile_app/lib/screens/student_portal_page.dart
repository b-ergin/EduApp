import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:mobile_app/models/quiz_item.dart';
import 'package:mobile_app/models/quiz_progress_state.dart';
import 'package:mobile_app/screens/question_flow_page.dart';
import 'package:mobile_app/services/api_service.dart';
import 'package:mobile_app/services/progress_storage_service.dart';
import 'package:mobile_app/widgets/adventure_map.dart';
import 'package:mobile_app/widgets/quiz_card.dart';
import 'package:shared_preferences/shared_preferences.dart';

class StudentPortalPage extends StatefulWidget {
  const StudentPortalPage({super.key});

  @override
  State<StudentPortalPage> createState() => _StudentPortalPageState();
}

class _UnlockResult {
  const _UnlockResult({
    required this.unlocked,
    this.reason,
    this.challengeWindow,
    this.challengeRequiredStars,
    this.challengeEarnedStars,
  });

  final bool unlocked;
  final String? reason;
  final int? challengeWindow;
  final int? challengeRequiredStars;
  final int? challengeEarnedStars;
}

class _LevelSnapshot {
  const _LevelSnapshot({
    required this.totalQuizzes,
    required this.completedQuizzes,
    required this.totalStars,
    required this.totalPossibleStars,
    required this.streak,
    required this.playerLevel,
    required this.levelXpProgress,
    required this.levelXpRequired,
    required this.levelXpCurrent,
  });

  final int totalQuizzes;
  final int completedQuizzes;
  final int totalStars;
  final int totalPossibleStars;
  final int streak;
  final int playerLevel;
  final double levelXpProgress;
  final int levelXpRequired;
  final int levelXpCurrent;
}

class _PlayerLevelInfo {
  const _PlayerLevelInfo({
    required this.level,
    required this.currentLevelXp,
    required this.xpToNextLevel,
    required this.progress,
  });

  final int level;
  final int currentLevelXp;
  final int xpToNextLevel;
  final double progress;
}

class _StudentPortalPageState extends State<StudentPortalPage> {
  static const String _fuzzyHappy = 'assets/mascot/fuzzy_happy.png';
  static const String _streakCountKey = 'eduapp_daily_streak_count_v1';
  static const String _streakAnchorDateKey = 'eduapp_daily_streak_anchor_v1';

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
  int _dailyStreakCount = 0;
  DateTime? _streakAnchorDate;

  @override
  void initState() {
    super.initState();
    bootstrap();
  }

  Future<void> bootstrap() async {
    await login();
    await loadQuizzes();
    await _loadProgressFromLocal();
    await _loadStreakFromLocal();
    await _ensureNonZeroStreak();
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

  Future<void> _loadStreakFromLocal() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final storedCount = prefs.getInt(_streakCountKey) ?? 0;
      final storedDate = prefs.getString(_streakAnchorDateKey);
      DateTime? parsed;
      if (storedDate != null && storedDate.trim().isNotEmpty) {
        parsed = DateTime.tryParse(storedDate);
      }
      _dailyStreakCount = storedCount;
      _streakAnchorDate = parsed;
    } catch (_) {
      // Ignore broken local streak cache.
    }
  }

  Future<void> _saveStreakToLocal() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setInt(_streakCountKey, _dailyStreakCount);
      if (_streakAnchorDate != null) {
        final normalized = DateTime(
          _streakAnchorDate!.year,
          _streakAnchorDate!.month,
          _streakAnchorDate!.day,
        );
        await prefs.setString(
          _streakAnchorDateKey,
          normalized.toIso8601String(),
        );
      }
    } catch (_) {
      // Ignore local write failure in prototype.
    }
  }

  Future<void> _ensureNonZeroStreak() async {
    if (_dailyStreakCount > 0 && _streakAnchorDate != null) return;
    _dailyStreakCount = 1;
    _streakAnchorDate = DateTime.now();
    await _saveStreakToLocal();
  }

  int _daysBetween(DateTime a, DateTime b) {
    final start = DateTime(a.year, a.month, a.day);
    final end = DateTime(b.year, b.month, b.day);
    return end.difference(start).inDays;
  }

  Future<void> _registerPlayActivity() async {
    final today = DateTime.now();
    if (_streakAnchorDate == null || _dailyStreakCount <= 0) {
      _dailyStreakCount = 1;
      _streakAnchorDate = today;
      await _saveStreakToLocal();
      return;
    }

    final dayGap = _daysBetween(_streakAnchorDate!, today);
    if (dayGap <= 0) {
      return; // already counted today
    }
    if (dayGap <= 2) {
      // Includes one missed day grace period.
      _dailyStreakCount += 1;
    } else {
      _dailyStreakCount = 1;
    }
    _streakAnchorDate = today;
    await _saveStreakToLocal();
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
    return _unlockFor(quiz, orderedQuizzes).unlocked;
  }

  _UnlockResult _unlockFor(QuizItem quiz, List<QuizItem> orderedQuizzes) {
    final index = orderedQuizzes.indexWhere((item) => item.id == quiz.id);
    if (index <= 0) return const _UnlockResult(unlocked: true);

    // Strict chain unlock: every prior node in this level path must be
    // completed at least once.
    for (int i = 0; i < index; i++) {
      final priorQuiz = orderedQuizzes[i];
      final done = progressByQuiz[priorQuiz.id]?.everCompleted ?? false;
      if (!done) {
        return const _UnlockResult(
          unlocked: false,
          reason: 'Finish previous nodes first.',
        );
      }
    }

    if (quiz.isChallenge) {
      final window = (quiz.challengeWindowSize ?? 0).clamp(1, index);
      final requiredStars = (quiz.challengeMinStars ?? 0).clamp(1, 999);
      final start = (index - window).clamp(0, index);
      final recent = orderedQuizzes.sublist(start, index);
      final earnedStars = recent.fold<int>(
        0,
        (sum, item) => sum + (progressByQuiz[item.id]?.stars ?? 0),
      );

      if (earnedStars < requiredStars) {
        return _UnlockResult(
          unlocked: false,
          reason: 'Need $requiredStars stars from last $window quizzes.',
          challengeWindow: window,
          challengeRequiredStars: requiredStars,
          challengeEarnedStars: earnedStars,
        );
      }
    }

    return const _UnlockResult(unlocked: true);
  }

  _PlayerLevelInfo _computePlayerLevel(int totalXp) {
    int level = 1;
    int remaining = totalXp;

    int xpNeededForLevel(int currentLevel) => 180 + ((currentLevel - 1) * 45);

    int threshold = xpNeededForLevel(level);
    while (remaining >= threshold) {
      remaining -= threshold;
      level += 1;
      threshold = xpNeededForLevel(level);
    }

    final progress = threshold <= 0 ? 0.0 : remaining / threshold;
    return _PlayerLevelInfo(
      level: level,
      currentLevelXp: remaining,
      xpToNextLevel: threshold,
      progress: progress.clamp(0, 1),
    );
  }

  int _quizEarnedXp(QuizItem quiz, QuizProgressState state) {
    if (!state.everCompleted) {
      return 0;
    }
    final xpWeight = quiz.xpWeight.clamp(1, 10);
    final questions = quiz.questionCount > 0 ? quiz.questionCount : 1;
    final cap = questions * xpWeight * 12;
    final scoreXp = ((cap * state.bestScorePercent) / 100).round();
    final completionBonus = xpWeight * 20;
    return scoreXp + completionBonus;
  }

  _LevelSnapshot _levelSnapshot(List<QuizItem> orderedQuizzes) {
    int completed = 0;
    int stars = 0;
    int totalXp = 0;

    for (final quiz in orderedQuizzes) {
      final state = progressByQuiz[quiz.id];
      if (state == null) continue;
      if (state.everCompleted) {
        completed++;
      }
      stars += state.stars;
    }

    // XP level is global across all quizzes, not limited to currently selected
    // grade path.
    for (final quiz in quizzes) {
      final state = progressByQuiz[quiz.id];
      if (state == null) continue;
      totalXp += _quizEarnedXp(quiz, state);
    }
    final levelInfo = _computePlayerLevel(totalXp);

    return _LevelSnapshot(
      totalQuizzes: orderedQuizzes.length,
      completedQuizzes: completed,
      totalStars: stars,
      totalPossibleStars: orderedQuizzes.length * 3,
      streak: _dailyStreakCount <= 0 ? 1 : _dailyStreakCount,
      playerLevel: levelInfo.level,
      levelXpProgress: levelInfo.progress,
      levelXpRequired: levelInfo.xpToNextLevel,
      levelXpCurrent: levelInfo.currentLevelXp,
    );
  }

  Future<void> startQuiz(QuizItem quiz) async {
    final state = progressByQuiz[quiz.id];
    if (state == null || token == null) return;

    int targetQuestionId = state.currentQuestionId ?? 0;
    final answeredBefore = state.answeredCount;
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

      // Retakes start as fresh attempts, but keep historical completion/unlock.
      if (state.everCompleted) {
        state.beginRetake(firstQuestionId: targetQuestionId);
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

    if (state.answeredCount > answeredBefore) {
      await _registerPlayActivity();
    }
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
    final levelSnapshot = _levelSnapshot(mapQuizzes);

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
                            const SizedBox(height: 4),
                            _buildProgressHeader(levelSnapshot),
                            _buildGuideBanner(),
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
                                ...visibleQuizzes.asMap().entries.map((entry) {
                                  final index = entry.key;
                                  final quiz = entry.value;
                                  final progress = progressByQuiz[quiz.id]!;
                                  final unlockState = _unlockFor(
                                    quiz,
                                    mapQuizzes,
                                  );
                                  return TweenAnimationBuilder<double>(
                                    tween: Tween(begin: 0, end: 1),
                                    duration: Duration(
                                      milliseconds: 280 + (index * 50),
                                    ),
                                    curve: Curves.easeOutCubic,
                                    builder: (context, t, child) {
                                      return Opacity(
                                        opacity: t,
                                        child: Transform.translate(
                                          offset: Offset(0, (1 - t) * 16),
                                          child: child,
                                        ),
                                      );
                                    },
                                    child: QuizCard(
                                      quiz: quiz,
                                      progress: progress,
                                      unlocked: unlockState.unlocked,
                                      unlockReason: unlockState.reason,
                                      challengeEarnedStars:
                                          unlockState.challengeEarnedStars,
                                      challengeRequiredStars:
                                          unlockState.challengeRequiredStars,
                                      onStart:
                                          unlockState.unlocked
                                              ? () => startQuiz(quiz)
                                              : null,
                                    ),
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

  Widget _buildProgressHeader(_LevelSnapshot snapshot) {
    final progress = snapshot.levelXpProgress;
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(14),
        gradient: const LinearGradient(
          colors: [Color(0xFF0F766E), Color(0xFF14B8A6)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        boxShadow: const [
          BoxShadow(
            color: Color(0x220F766E),
            blurRadius: 12,
            offset: Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        children: [
          Row(
            children: [
              _statPill(
                icon: Icons.rocket_launch_rounded,
                label: 'Level ${snapshot.playerLevel}',
              ),
              const SizedBox(width: 6),
              _statPill(
                icon: Icons.local_fire_department_rounded,
                label: '${snapshot.streak} streak',
              ),
              const Spacer(),
              Text(
                '${snapshot.totalStars}/${snapshot.totalPossibleStars} stars',
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w700,
                  fontSize: 12,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          ClipRRect(
            borderRadius: BorderRadius.circular(999),
            child: LinearProgressIndicator(
              value: progress,
              minHeight: 8,
              backgroundColor: const Color(0x55FFFFFF),
              valueColor: const AlwaysStoppedAnimation<Color>(Colors.white),
            ),
          ),
          const SizedBox(height: 4),
          Align(
            alignment: Alignment.centerRight,
            child: Text(
              'Level ${snapshot.playerLevel} • ${snapshot.levelXpCurrent}/${snapshot.levelXpRequired} XP',
              style: const TextStyle(color: Colors.white70, fontSize: 11.5),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildGuideBanner() {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
      decoration: BoxDecoration(
        color: const Color(0xFFF0FDFA),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFF99F6E4)),
      ),
      child: Row(
        children: [
          TweenAnimationBuilder<double>(
            tween: Tween(begin: 0.95, end: 1.05),
            duration: const Duration(milliseconds: 900),
            curve: Curves.easeInOut,
            builder: (context, scale, child) {
              return Transform.scale(scale: scale, child: child);
            },
            child: Image.asset(_fuzzyHappy, width: 42, height: 42),
          ),
          const SizedBox(width: 8),
          const Expanded(
            child: Text(
              'Fuzzy tip: Finish nodes in order, then unlock challenge levels with stars.',
              style: TextStyle(
                color: Color(0xFF0F766E),
                fontWeight: FontWeight.w700,
                fontSize: 12,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _statPill({required IconData icon, required String label}) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
      decoration: BoxDecoration(
        color: const Color(0x2AFFFFFF),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: Colors.white),
          const SizedBox(width: 4),
          Text(
            label,
            style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.w700,
              fontSize: 12,
            ),
          ),
        ],
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
