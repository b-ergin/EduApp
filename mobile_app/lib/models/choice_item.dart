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
