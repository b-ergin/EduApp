import 'package:flutter/material.dart';
import 'package:mobile_app/models/answer_result.dart';
import 'package:mobile_app/models/choice_item.dart';
import 'package:mobile_app/models/question_payload.dart';
import 'package:mobile_app/models/quiz_item.dart';
import 'package:mobile_app/models/quiz_progress_state.dart';
import 'package:mobile_app/services/api_service.dart';

class QuestionFlowPage extends StatefulWidget {
  const QuestionFlowPage({
    super.key,
    required this.apiService,
    required this.token,
    required this.quiz,
    required this.initialQuestionId,
    required this.state,
    required this.onProgressChanged,
  });

  final ApiService apiService;
  final String token;
  final QuizItem quiz;
  final int initialQuestionId;
  final QuizProgressState state;
  final VoidCallback onProgressChanged;

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
      final payload = await widget.apiService.fetchQuestion(
        token: widget.token,
        quizId: widget.quiz.id,
        questionId: questionId,
      );
      widget.state.currentQuestionId = payload.questionId;
      widget.state.completed = false;

      setState(() {
        question = payload;
        loading = false;
      });
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
      final payload = await widget.apiService.submitAnswer(
        token: widget.token,
        questionId: current.questionId,
        choiceId: selectedChoiceId!,
      );
      _applyAnswer(payload, current.questionId);
    } catch (e) {
      setState(() {
        error = 'Submit error: $e';
      });
    }
  }

  void _applyAnswer(AnswerResult payload, int questionId) {
    widget.state.answeredQuestionIds.add(questionId);
    if (payload.isCorrect) {
      widget.state.correctQuestionIds.add(questionId);
    }
    widget.state.currentQuestionId = payload.nextQuestionId;
    widget.state.completed = payload.finished;
    widget.onProgressChanged();

    setState(() {
      answered = true;
      result = payload.isCorrect;
      correctChoiceId = payload.correctChoiceId;
      nextQuestionId = payload.nextQuestionId;
      finished = payload.finished;
    });
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
        child:
            loading
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
                  blurRadius: 18,
                  offset: Offset(0, 8),
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
                      onPressed:
                          selectedChoiceId == null ? null : _submitAnswer,
                      style: FilledButton.styleFrom(
                        backgroundColor: const Color(0xFF0F766E),
                        foregroundColor: Colors.white,
                      ),
                      child: const Text('Submit Answer'),
                    ),
                  ),
                if (answered) ...[
                  const SizedBox(height: 10),
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color:
                          (result ?? false)
                              ? const Color(0xFFDCFCE7)
                              : const Color(0xFFFEE2E2),
                      border: Border.all(
                        color:
                            (result ?? false)
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
                        color:
                            (result ?? false)
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
                        foregroundColor: Colors.white,
                      ),
                      child: Text(
                        finished
                            ? 'Back to Quiz Selection'
                            : 'Continue to Next Question',
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
        onChanged:
            answered
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
      return '${widget.apiService.baseUrl}$trimmed';
    }
    return '${widget.apiService.baseUrl}/$trimmed';
  }
}
