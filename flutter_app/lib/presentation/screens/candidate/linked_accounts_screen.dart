import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/constants/app_colors.dart';
import '../../../data/repositories/social_auth_repository.dart';

/// Phase 5 — manage the social providers attached to the current user.
class LinkedAccountsScreen extends ConsumerStatefulWidget {
  const LinkedAccountsScreen({super.key});

  @override
  ConsumerState<LinkedAccountsScreen> createState() => _LinkedAccountsScreenState();
}

class _LinkedAccountsScreenState extends ConsumerState<LinkedAccountsScreen> {
  bool _loading = true;
  bool _hasPassword = false;
  List<LinkedProvider> _providers = const [];
  String? _busy;

  @override
  void initState() {
    super.initState();
    _refresh();
  }

  Future<void> _refresh() async {
    setState(() => _loading = true);
    try {
      final result = await SocialAuthRepository().listProviders();
      if (!mounted) return;
      setState(() {
        _hasPassword = result.hasPassword;
        _providers = result.providers;
      });
    } catch (e) {
      _toast(e.toString(), error: true);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _toast(String text, {bool error = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(text),
      backgroundColor: error ? AppColors.error : AppColors.success,
    ));
  }

  bool get _canUnlink => _hasPassword || _providers.length > 1;

  Future<void> _linkGoogle() async {
    setState(() => _busy = 'google');
    try {
      final ok = await SocialAuthRepository().linkGoogle();
      if (ok) {
        await _refresh();
        _toast('Compte Google lié.');
      }
    } catch (e) {
      _toast(e.toString(), error: true);
    } finally {
      if (mounted) setState(() => _busy = null);
    }
  }

  Future<void> _linkFacebook() async {
    setState(() => _busy = 'facebook');
    try {
      final ok = await SocialAuthRepository().linkFacebook();
      if (ok) {
        await _refresh();
        _toast('Compte Facebook lié.');
      }
    } catch (e) {
      _toast(e.toString(), error: true);
    } finally {
      if (mounted) setState(() => _busy = null);
    }
  }

  Future<void> _unlink(LinkedProvider p) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: Text('Délier ${p.provider} ?'),
        content: const Text('Vous ne pourrez plus vous connecter avec cette méthode.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Annuler')),
          TextButton(onPressed: () => Navigator.pop(context, true), child: const Text('Délier')),
        ],
      ),
    );
    if (confirmed != true) return;

    setState(() => _busy = 'unlink:${p.id}');
    try {
      await SocialAuthRepository().unlinkProvider(p.id);
      await _refresh();
      _toast('Méthode de connexion supprimée.');
    } catch (e) {
      _toast(e.toString(), error: true);
    } finally {
      if (mounted) setState(() => _busy = null);
    }
  }

  Future<void> _openSetPasswordSheet() async {
    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (_) => _SetPasswordSheet(hasPassword: _hasPassword, onDone: _refresh),
    );
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(
      title: const Text('Comptes liés'),
      leading: IconButton(
        icon: const Icon(Icons.arrow_back),
        onPressed: () => context.pop(),
      ),
    ),
    body: SafeArea(
      child: RefreshIndicator(
        onRefresh: _refresh,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : ListView(
                padding: const EdgeInsets.all(20),
                children: [
                  Text(
                    'Liez vos comptes Google ou Facebook pour vous connecter plus rapidement.',
                    style: TextStyle(color: AppColors.muted, fontSize: 13.5, height: 1.5),
                  ),
                  const SizedBox(height: 20),
                  _providerTile(
                    provider: 'google',
                    name: 'Google',
                    icon: const Icon(Icons.g_mobiledata, color: Color(0xFF4285F4), size: 32),
                    linked: _providers.firstWhere(
                      (p) => p.provider == 'google',
                      orElse: () => LinkedProvider(id: '', provider: 'google', displayId: '', linkedAt: DateTime.now()),
                    ),
                    onLink: _linkGoogle,
                  ),
                  const SizedBox(height: 10),
                  _providerTile(
                    provider: 'facebook',
                    name: 'Facebook',
                    icon: Container(
                      width: 32, height: 32,
                      decoration: const BoxDecoration(
                        color: Color(0xFF1877F2), shape: BoxShape.circle,
                      ),
                      child: const Center(
                        child: Text('f', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 20)),
                      ),
                    ),
                    linked: _providers.firstWhere(
                      (p) => p.provider == 'facebook',
                      orElse: () => LinkedProvider(id: '', provider: 'facebook', displayId: '', linkedAt: DateTime.now()),
                    ),
                    onLink: _linkFacebook,
                  ),
                  const SizedBox(height: 10),
                  // Password row.
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: AppColors.surface2,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: Row(
                      children: [
                        Container(
                          width: 32, height: 32,
                          decoration: BoxDecoration(
                            color: AppColors.mint.withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Icon(Icons.key_outlined, color: AppColors.mint, size: 18),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Mot de passe', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                              const SizedBox(height: 2),
                              Text(
                                _hasPassword ? 'Défini' : 'Non défini',
                                style: TextStyle(
                                  color: _hasPassword ? AppColors.success : AppColors.muted,
                                  fontSize: 12,
                                ),
                              ),
                            ],
                          ),
                        ),
                        TextButton(
                          onPressed: _openSetPasswordSheet,
                          child: Text(_hasPassword ? 'Changer' : 'Définir'),
                        ),
                      ],
                    ),
                  ),
                  if (!_canUnlink) Padding(
                    padding: const EdgeInsets.only(top: 16),
                    child: Text(
                      'Définissez un mot de passe pour pouvoir délier votre dernière méthode de connexion.',
                      style: TextStyle(color: AppColors.warning, fontSize: 12.5),
                    ),
                  ),
                ],
              ),
      ),
    ),
  );

  Widget _providerTile({
    required String provider,
    required String name,
    required Widget icon,
    required LinkedProvider linked,
    required Future<void> Function() onLink,
  }) {
    final isLinked = linked.id.isNotEmpty;
    final unlinkBusy = _busy == 'unlink:${linked.id}';
    final linkBusy = _busy == provider;

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface2,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.border),
      ),
      child: Row(
        children: [
          icon,
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(name, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                const SizedBox(height: 2),
                Text(
                  isLinked ? 'Lié · id ${linked.displayId}' : 'Non lié',
                  style: TextStyle(
                    color: isLinked ? AppColors.success : AppColors.muted,
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),
          if (isLinked)
            TextButton(
              onPressed: _canUnlink && !unlinkBusy ? () => _unlink(linked) : null,
              style: TextButton.styleFrom(foregroundColor: AppColors.error),
              child: Text(unlinkBusy ? '…' : 'Délier'),
            )
          else
            FilledButton(
              onPressed: linkBusy ? null : onLink,
              child: Text(linkBusy ? 'Liaison…' : 'Lier'),
            ),
        ],
      ),
    );
  }
}

/// Bottom sheet for setting / changing the local password.
class _SetPasswordSheet extends StatefulWidget {
  const _SetPasswordSheet({required this.hasPassword, required this.onDone});

  final bool hasPassword;
  final Future<void> Function() onDone;

  @override
  State<_SetPasswordSheet> createState() => _SetPasswordSheetState();
}

class _SetPasswordSheetState extends State<_SetPasswordSheet> {
  final _form = GlobalKey<FormState>();
  final _current = TextEditingController();
  final _next = TextEditingController();
  final _confirm = TextEditingController();
  bool _busy = false;

  @override
  void dispose() {
    _current.dispose();
    _next.dispose();
    _confirm.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    if (_next.text != _confirm.text) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Les mots de passe ne correspondent pas.'), backgroundColor: AppColors.error),
      );
      return;
    }
    setState(() => _busy = true);
    try {
      await SocialAuthRepository().setPassword(
        currentPassword: widget.hasPassword ? _current.text : null,
        password: _next.text,
      );
      await widget.onDone();
      if (mounted) Navigator.pop(context);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppColors.error),
        );
      }
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final bottomInset = MediaQuery.of(context).viewInsets.bottom;
    return Padding(
      padding: EdgeInsets.fromLTRB(24, 24, 24, bottomInset + 24),
      child: Form(
        key: _form,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              widget.hasPassword ? 'Changer le mot de passe' : 'Définir un mot de passe',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            if (widget.hasPassword) ...[
              TextFormField(
                controller: _current,
                obscureText: true,
                decoration: const InputDecoration(labelText: 'Mot de passe actuel'),
                validator: (v) => (v == null || v.length < 8) ? '8 caractères minimum' : null,
              ),
              const SizedBox(height: 12),
            ],
            TextFormField(
              controller: _next,
              obscureText: true,
              decoration: const InputDecoration(labelText: 'Nouveau mot de passe'),
              validator: (v) => (v == null || v.length < 8) ? '8 caractères minimum' : null,
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _confirm,
              obscureText: true,
              decoration: const InputDecoration(labelText: 'Confirmer'),
              validator: (v) => (v == null || v.length < 8) ? '8 caractères minimum' : null,
            ),
            const SizedBox(height: 20),
            FilledButton(
              onPressed: _busy ? null : _submit,
              child: _busy
                  ? const SizedBox(
                      height: 18, width: 18,
                      child: CircularProgressIndicator(color: AppColors.bg, strokeWidth: 2),
                    )
                  : Text(widget.hasPassword ? 'Modifier' : 'Définir'),
            ),
          ],
        ),
      ),
    );
  }
}
