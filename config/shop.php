<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Shop details
    |--------------------------------------------------------------------------
    | The name comes from APP_NAME. These are the words around it.
    */

    'tagline' => "Stories you can't put down.",

    'source_url' => env('SHOP_SOURCE_URL'),      // "source" link in the footer (GitHub)
    'home_url' => env('SHOP_HOME_URL'),          // "back to portfolio" link in the footer

    /*
    |--------------------------------------------------------------------------
    | Public demo mode
    |--------------------------------------------------------------------------
    | With SHOP_DEMO=true the site is safe to leave open to the public: whatever
    | a visitor makes (account, upload, review, comment) is only shown to their
    | own browser and is deleted after "sandbox_hours". See App\Support\Sandbox.
    */

    'demo' => (bool) env('SHOP_DEMO', false),

    'sandbox_hours' => (int) env('SHOP_SANDBOX_HOURS', 24),

    'sandbox_max_uploads' => (int) env('SHOP_SANDBOX_MAX_UPLOADS', 5),

    // Run the migrations and seeders on the first request (for hosts with no command line).
    'auto_install' => (bool) env('SHOP_AUTO_INSTALL', false),

    /*
    |--------------------------------------------------------------------------
    | Accounts made by the seeder
    |--------------------------------------------------------------------------
    | The owner gets the Super-Admin role. Give OWNER_PASSWORD_HASH (bcrypt) on a
    | server so the plain password is never stored there, or OWNER_PASSWORD locally.
    | The two demo accounts are only seeded in demo mode and on a local machine.
    */

    'owner' => [
        'name' => env('OWNER_NAME', 'AstroQuack'),
        'email' => env('OWNER_EMAIL'),
        'password' => env('OWNER_PASSWORD'),
        'password_hash' => env('OWNER_PASSWORD_HASH'),
    ],

    'demo_accounts' => [
        'reader' => ['name' => 'Demo Reader', 'email' => 'reader@example.com', 'password' => 'turn-the-page', 'role' => null],
        'admin' => ['name' => 'Demo Admin', 'email' => 'admin@example.com', 'password' => 'shelf-control', 'role' => 'Admin'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload limits (kilobytes)
    |--------------------------------------------------------------------------
    | The host's own PHP limits (upload_max_filesize, post_max_size) still apply.
    */

    'max_kb' => [
        'cover' => (int) env('SHOP_MAX_COVER_KB', 2048),
        'book' => (int) env('SHOP_MAX_BOOK_KB', 10240),
        'audio' => (int) env('SHOP_MAX_AUDIO_KB', 20480),
        'video' => (int) env('SHOP_MAX_VIDEO_KB', 40960),
    ],

    'per_page' => 12,

];
