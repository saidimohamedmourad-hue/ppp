import 'package:flutter/material.dart';
import '../../../core/constants/app_colors.dart';

class NotificationsScreen extends StatelessWidget {
  const NotificationsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Notifications')),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.notifications_none, size: 72, color: AppColors.grey),
            const SizedBox(height: 16),
            const Text(
              'Aucune notification',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600, color: AppColors.dark),
            ),
            const SizedBox(height: 8),
            const Text(
              'Vous serez notifié ici lors de\nnouvelles candidatures ou messages.',
              textAlign: TextAlign.center,
              style: TextStyle(color: AppColors.grey),
            ),
          ],
        ),
      ),
    );
  }
}
