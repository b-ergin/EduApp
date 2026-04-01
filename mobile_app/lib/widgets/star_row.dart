import 'package:flutter/material.dart';

class StarRow extends StatelessWidget {
  const StarRow({super.key, required this.stars});

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
