import 'dart:async';

import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_sign_in/google_sign_in.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/google_web_button.dart';
import '../../../core/constants/app_colors.dart';
import '../../../data/repositories/social_auth_repository.dart';

class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key});

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen> {
  final _form = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _phone = TextEditingController();
  final _password = TextEditingController();
  String _role = 'job-seeker';
  bool _loading = false;
  bool _socialBusy = false;

  // Web only: GIS delivers the account through this stream after the user
  // clicks the rendered button (GoogleSignIn.signIn() throws on web).
  StreamSubscription<GoogleSignInAccount?>? _googleWebSub;
  final GoogleSignIn _webGoogle = GoogleSignIn(scopes: const ['email', 'profile', 'openid']);

  // Owners must enter a phone number now (it's the public contact on their
  // jobs / training sessions). Candidates can leave it for later.
  bool get _phoneRequired => _role == 'company-owner' || _role == 'school-owner';

  @override
  void initState() {
    super.initState();
    if (kIsWeb) {
      _googleWebSub = _webGoogle.onCurrentUserChanged.listen((account) {
        if (account != null) _completeWebGoogle(account);
      });
    }
  }

  @override
  void dispose() {
    _googleWebSub?.cancel();
    _name.dispose();
    _email.dispose();
    _phone.dispose();
    _password.dispose();
    super.dispose();
  }

  // Après une auth sociale réussie (jeton déjà persisté par le repo), on
  // rafraîchit l'état pour que le routeur redirige vers le bon espace.
  Future<void> _afterSocial() async {
    await ref.read(authProvider.notifier).refreshUser();
  }

  void _socialError(Object e) {
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString()), backgroundColor: AppColors.error),
      );
    }
  }

  /// Web : échange le compte GIS contre un jeton (avec le rôle choisi).
  Future<void> _completeWebGoogle(GoogleSignInAccount account) async {
    setState(() => _socialBusy = true);
    try {
      await SocialAuthRepository().completeGoogleFromAccount(account, role: _role);
      await _afterSocial();
    } catch (e) {
      await _webGoogle.signOut().catchError((_) => null);
      _socialError(e);
    } finally {
      if (mounted) setState(() => _socialBusy = false);
    }
  }

  /// Mobile : flux Google natif (avec le rôle choisi).
  Future<void> _googleMobile() async {
    setState(() => _socialBusy = true);
    try {
      final r = await SocialAuthRepository().signInWithGoogle(role: _role);
      if (r != null) await _afterSocial();
    } catch (e) {
      _socialError(e);
    } finally {
      if (mounted) setState(() => _socialBusy = false);
    }
  }

  Future<void> _facebook() async {
    setState(() => _socialBusy = true);
    try {
      final r = await SocialAuthRepository().signInWithFacebook(role: _role);
      if (r != null) await _afterSocial();
    } catch (e) {
      _socialError(e);
    } finally {
      if (mounted) setState(() => _socialBusy = false);
    }
  }

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    setState(() => _loading = true);
    try {
      await ref
          .read(authProvider.notifier)
          .register(
            name: _name.text.trim(),
            email: _email.text.trim(),
            password: _password.text,
            role: _role,
            phone: _phone.text.trim().isEmpty ? null : _phone.text.trim(),
          );
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(e.toString()),
            backgroundColor: AppColors.error,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    body: SafeArea(
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Form(
          key: _form,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 24),
              Center(
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(24),
                  child: Image.asset(
                    'assets/images/iqra_logo.png',
                    width: 120,
                    height: 120,
                    fit: BoxFit.cover,
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Text(
                'Créer un compte',
                style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 32),
              TextFormField(
                controller: _name,
                decoration: const InputDecoration(
                  labelText: 'Nom complet',
                  prefixIcon: Icon(Icons.person_outline),
                ),
                validator: (v) =>
                    (v == null || v.isEmpty) ? 'Nom requis' : null,
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _email,
                keyboardType: TextInputType.emailAddress,
                decoration: const InputDecoration(
                  labelText: 'Email',
                  prefixIcon: Icon(Icons.email_outlined),
                ),
                validator: (v) =>
                    (v == null || !v.contains('@')) ? 'Email invalide' : null,
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _phone,
                keyboardType: TextInputType.phone,
                autofillHints: const [AutofillHints.telephoneNumber],
                decoration: InputDecoration(
                  labelText: _phoneRequired
                      ? 'Téléphone de contact *'
                      : 'Téléphone (optionnel)',
                  helperText: _phoneRequired
                      ? 'Affiché aux candidats sur vos annonces'
                      : 'Requis avant votre première candidature',
                  prefixIcon: const Icon(Icons.phone_outlined),
                ),
                validator: (v) {
                  final t = (v ?? '').trim();
                  if (!_phoneRequired && t.isEmpty) return null;
                  if (t.length < 6) return 'Numéro trop court';
                  if (!RegExp(r'^[0-9+\-\s()]+$').hasMatch(t)) {
                    return 'Caractères invalides';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _password,
                obscureText: true,
                decoration: const InputDecoration(
                  labelText: 'Mot de passe',
                  prefixIcon: Icon(Icons.lock_outline),
                ),
                validator: (v) =>
                    (v == null || v.length < 8) ? 'Minimum 8 caractères' : null,
              ),
              const SizedBox(height: 16),
              Text(
                'Type de compte',
                style: Theme.of(
                  context,
                ).textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.w600),
              ),
              const SizedBox(height: 8),
              _RoleSelector(
                value: _role,
                onChanged: (r) => setState(() => _role = r),
              ),
              const SizedBox(height: 32),
              ElevatedButton(
                onPressed: _loading ? null : _submit,
                child: _loading
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(
                          color: Colors.white,
                          strokeWidth: 2,
                        ),
                      )
                    : const Text("S'inscrire"),
              ),

              // ── Inscription sociale (tous les profils ; le rôle choisi est
              //    transmis au backend, appliqué à la création du compte) ──
              const SizedBox(height: 20),
              Row(
                children: const [
                  Expanded(child: Divider()),
                  Padding(
                    padding: EdgeInsets.symmetric(horizontal: 10),
                    child: Text('OU', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.muted)),
                  ),
                  Expanded(child: Divider()),
                ],
              ),
              const SizedBox(height: 14),
              if (_socialBusy)
                const SizedBox(height: 48, child: Center(child: SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2))))
              else ...[
                if (kIsWeb)
                  Align(alignment: Alignment.center, child: googleRenderButton())
                else
                  OutlinedButton.icon(
                    onPressed: _googleMobile,
                    icon: const Icon(Icons.g_mobiledata, size: 26),
                    label: const Text('Continuer avec Google'),
                    style: OutlinedButton.styleFrom(minimumSize: const Size(double.infinity, 50)),
                  ),
                const SizedBox(height: 10),
                ElevatedButton.icon(
                  onPressed: _facebook,
                  icon: const Icon(Icons.facebook, size: 20),
                  label: const Text('Continuer avec Facebook'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF1877F2),
                    foregroundColor: Colors.white,
                    minimumSize: const Size(double.infinity, 50),
                  ),
                ),
              ],

              const SizedBox(height: 16),
              Center(
                child: TextButton(
                  onPressed: () => context.go('/login'),
                  child: const Text('Déjà un compte ? Se connecter'),
                ),
              ),
            ],
          ),
        ),
      ),
    ),
  );
}

class _RoleSelector extends StatelessWidget {
  const _RoleSelector({required this.value, required this.onChanged});
  final String value;
  final ValueChanged<String> onChanged;

  @override
  Widget build(BuildContext context) {
    const roles = [
      ('job-seeker', 'Candidat', Icons.person_search),
      ('company-owner', 'Entreprise', Icons.business),
      ('school-owner', 'École', Icons.school),
    ];

    return Row(
      children: roles.map((r) {
        final selected = value == r.$1;
        return Expanded(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 4),
            child: GestureDetector(
              onTap: () => onChanged(r.$1),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                padding: const EdgeInsets.symmetric(vertical: 12),
                decoration: BoxDecoration(
                  color: selected ? AppColors.primary : AppColors.lightGrey,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Column(
                  children: [
                    Icon(
                      r.$3,
                      color: selected ? Colors.white : AppColors.grey,
                      size: 22,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      r.$2,
                      style: TextStyle(
                        fontSize: 11,
                        color: selected ? Colors.white : AppColors.grey,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        );
      }).toList(),
    );
  }
}
