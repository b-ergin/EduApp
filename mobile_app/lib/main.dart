import 'package:flutter/material.dart';
import 'package:mobile_app/screens/student_portal_page.dart';

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
