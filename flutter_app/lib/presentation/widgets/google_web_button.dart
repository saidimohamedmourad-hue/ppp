// Resolves `googleRenderButton()` to the real GIS button on web and to a
// harmless no-op widget everywhere else, so the same import works on every
// platform without pulling `google_sign_in_web` into mobile builds.
export 'google_web_button_stub.dart'
    if (dart.library.html) 'google_web_button_web.dart';
