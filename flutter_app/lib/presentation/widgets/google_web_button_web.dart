import 'package:flutter/widgets.dart';
// google_sign_in_web is a transitive dep (federated plugin of google_sign_in);
// its web_only library is only imported on web builds via conditional import.
// ignore: depend_on_referenced_packages
import 'package:google_sign_in_web/web_only.dart' as web;

/// Renders the official Google Identity Services (GIS) sign-in button.
///
/// On the web this is the ONLY way to start the credential flow that returns
/// an ID token — `GoogleSignIn.signIn()` throws `UnimplementedError` in the
/// browser since the plugin migrated to GIS. The client ID is read from the
/// `<meta name="google-signin-client_id">` tag in web/index.html.
Widget googleRenderButton() => web.renderButton();
