import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../providers/auth_provider.dart';
import '../../../core/constants/app_colors.dart';

class ProfileScreen extends ConsumerWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authProvider).valueOrNull;

    return Scaffold(
      appBar: AppBar(title: const Text('Mon profil')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            CircleAvatar(
              radius: 50,
              backgroundColor: AppColors.primary.withValues(alpha: 0.1),
              child: Text(
                (user?.name.isNotEmpty == true) ? user!.name[0].toUpperCase() : '?',
                style: const TextStyle(fontSize: 36, fontWeight: FontWeight.bold, color: AppColors.primary),
              ),
            ),
            const SizedBox(height: 16),
            Text(user?.name ?? '', style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold)),
            const SizedBox(height: 4),
            Text(user?.email ?? '', style: const TextStyle(color: AppColors.grey)),
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
              decoration: BoxDecoration(color: AppColors.primary.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(20)),
              child: Text(user?.role ?? '', style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.w600)),
            ),
            const SizedBox(height: 32),
            _MenuItem(icon: Icons.assignment_outlined, label: 'Mes candidatures', onTap: () => context.push('/my-applications')),
            _MenuItem(icon: Icons.description_outlined, label: 'Mes CVs', onTap: () => context.push('/my-cvs')),
            _MenuItem(icon: Icons.notifications_outlined, label: 'Notifications', onTap: () => context.push('/notifications')),
            _MenuItem(icon: Icons.link_outlined, label: 'Comptes liés', onTap: () => context.push('/profile/linked-accounts')),
            _MenuItem(icon: Icons.settings_outlined, label: 'Paramètres', onTap: () => context.push('/settings')),
            const Divider(height: 32),
            _MenuItem(
              icon: Icons.logout,
              label: 'Se déconnecter',
              color: AppColors.error,
              onTap: () async {
                await ref.read(authProvider.notifier).logout();
                if (context.mounted) context.go('/login');
              },
            ),
          ],
        ),
      ),
    );
  }
}

class _MenuItem extends StatelessWidget {
  const _MenuItem({required this.icon, required this.label, required this.onTap, this.color = AppColors.dark});
  final IconData icon;
  final String label;
  final VoidCallback onTap;
  final Color color;

  @override
  Widget build(BuildContext context) => ListTile(
    leading: Icon(icon, color: color),
    title: Text(label, style: TextStyle(color: color, fontWeight: FontWeight.w500)),
    trailing: Icon(Icons.chevron_right, color: color.withValues(alpha: 0.5)),
    onTap: onTap,
    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
  );
}

