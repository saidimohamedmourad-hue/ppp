<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // ── Google OAuth (Phase 2 of PLAN_AUTH_SOCIAL.md) ─────────────────────────
    // Each surface (web / Android / iOS) gets a separate OAuth client at
    // Google Cloud — list whichever ones are configured. When non-empty, the
    // backend rejects ID tokens issued for any other audience.
    'google' => [
        'web_client_id'     => env('GOOGLE_WEB_CLIENT_ID'),
        'android_client_id' => env('GOOGLE_ANDROID_CLIENT_ID'),
        'ios_client_id'     => env('GOOGLE_IOS_CLIENT_ID'),
    ],

    // ── Cloudflare Turnstile (Phase 6 hardening) ──────────────────────────────
    // When `secret` is set, /forgot-password requires a fresh widget token.
    // Leave both blank in dev to disable.
    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret'   => env('TURNSTILE_SECRET'),
    ],

    // ── Facebook Login (Phase 3 of PLAN_AUTH_SOCIAL.md) ───────────────────────
    // One Meta app, used by web + Android + iOS. The secret is required
    // server-side to call /debug_token.
    // Instagram users get to log in here too, provided their Instagram is
    // linked to a Facebook account (the only path Meta supports since 2024).
    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
    ],

];
