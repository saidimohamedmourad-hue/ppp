import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/constants/app_colors.dart';
import '../../../data/repositories/auth_repository.dart';

/// Step 1 of password recovery: the user enters their email and we ask the
/// backend to send a reset link. The success UI is the same regardless of
/// whether the email is registered (anti-enumeration).
class ForgotPasswordScreen extends ConsumerStatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  ConsumerState<ForgotPasswordScreen> createState() =>
      _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends ConsumerState<ForgotPasswordScreen> {
  final _form = GlobalKey<FormState>();
  final _email = TextEditingController();
  bool _loading = false;
  bool _sent = false;

  @override
  void dispose() {
    _email.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    setState(() => _loading = true);
    try {
      await AuthRepository().forgotPassword(_email.text.trim());
      if (mounted) setState(() => _sent = true);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppColors.error),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(
      leading: IconButton(
        icon: const Icon(Icons.arrow_back),
        onPressed: () => context.go('/login'),
      ),
    ),
    body: SafeArea(
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: _sent ? _buildSuccess() : _buildForm(),
      ),
    ),
  );

  Widget _buildForm() => Form(
    key: _form,
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const SizedBox(height: 16),
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppColors.mint.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(16),
          ),
          child: const Icon(Icons.lock_reset, color: AppColors.mint, size: 32),
        ),
        const SizedBox(height: 24),
        Text(
          'Mot de passe oublié ?',
          style: Theme.of(context).textTheme.headlineMedium?.copyWith(fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 8),
        Text(
          'Entrez votre adresse e-mail et nous vous enverrons un lien pour réinitialiser votre mot de passe.',
          style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: AppColors.muted),
        ),
        const SizedBox(height: 32),
        TextFormField(
          controller: _email,
          keyboardType: TextInputType.emailAddress,
          autofillHints: const [AutofillHints.email],
          decoration: const InputDecoration(
            labelText: 'Email',
            prefixIcon: Icon(Icons.email_outlined),
          ),
          validator: (v) => (v == null || !v.contains('@')) ? 'Email invalide' : null,
        ),
        const SizedBox(height: 32),
        ElevatedButton(
          onPressed: _loading ? null : _submit,
          child: _loading
              ? const SizedBox(
                  height: 20, width: 20,
                  child: CircularProgressIndicator(color: AppColors.bg, strokeWidth: 2),
                )
              : const Text('Envoyer le lien'),
        ),
        const SizedBox(height: 16),
        Center(
          child: TextButton(
            onPressed: () => context.go('/login'),
            child: const Text('← Retour à la connexion'),
          ),
        ),
      ],
    ),
  );

  Widget _buildSuccess() => Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      const SizedBox(height: 32),
      Center(
        child: Container(
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: AppColors.mint.withValues(alpha: 0.12),
            shape: BoxShape.circle,
          ),
          child: const Icon(Icons.mark_email_read_outlined, color: AppColors.mint, size: 48),
        ),
      ),
      const SizedBox(height: 32),
      Text(
        'Vérifiez votre boîte mail',
        style: Theme.of(context).textTheme.headlineMedium?.copyWith(fontWeight: FontWeight.bold),
        textAlign: TextAlign.center,
      ),
      const SizedBox(height: 12),
      Text(
        "Si un compte est associé à ${_email.text.trim()}, un email contenant un lien de réinitialisation vient d'être envoyé.\n\nPensez à vérifier vos spams.",
        style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: AppColors.muted, height: 1.6),
        textAlign: TextAlign.center,
      ),
      const SizedBox(height: 40),
      ElevatedButton(
        onPressed: () => context.go('/login'),
        child: const Text('Retour à la connexion'),
      ),
      const SizedBox(height: 12),
      TextButton(
        onPressed: () => setState(() => _sent = false),
        child: const Text("Je n'ai rien reçu, renvoyer"),
      ),
    ],
  );
}
