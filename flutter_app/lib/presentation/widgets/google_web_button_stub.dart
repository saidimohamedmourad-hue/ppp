import 'package:flutter/widgets.dart';

/// Non-web platforms never render the Google Identity Services button — they
/// use the native `GoogleSignIn.signIn()` flow instead. This stub keeps the
/// shared import resolvable on Android / iOS / desktop builds.
Widget googleRenderButton() => const SizedBox.shrink();
