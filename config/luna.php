<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Server Side Rendering
    |--------------------------------------------------------------------------
    |
    | These options configures if and how Luna uses Server Side Rendering
    | to pre-render the initial visits made to your application's pages.
    |
    | You can specify a custom SSR bundle path, or omit it to let Luna
    | try and automatically detect it for you.
    |
    | Do note that enabling these options will NOT automatically make SSR work,
    | as a separate rendering service needs to be available.
    |
    */

    'ssr' => [

        'enabled' => (bool) env('LUNA_SSR_ENABLED', true),

        'runtime' => env('LUNA_SSR_RUNTIME', 'node'),

        'ensure_runtime_exists' => (bool) env('LUNA_SSR_ENSURE_RUNTIME_EXISTS', false),

        'url' => env('LUNA_SSR_URL', 'http://127.0.0.1:13714'),

        'ensure_bundle_exists' => (bool) env('LUNA_SSR_ENSURE_BUNDLE_EXISTS', true),

        // 'bundle' => base_path('bootstrap/ssr/ssr.mjs'),

        /*
        |--------------------------------------------------------------------------
        | SSR Error Handling
        |--------------------------------------------------------------------------
        |
        | When SSR rendering fails, Luna gracefully falls back to client-side
        | rendering. Set throw_on_error to true to throw an exception instead.
        | This is useful for E2E testing where you want SSR errors to fail loudly.
        |
        | You can also listen for the LaraGram\Luna\Ssr\SsrRenderFailed event to handle
        | failures in your own way (e.g., logging, error tracking service).
        |
        */

        'throw_on_error' => (bool) env('LUNA_SSR_THROW_ON_ERROR', false),

    ],

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    |
    | Set `ensure_pages_exist` to true if you want to enforce that Luna page
    | components exist on disk when rendering a page. This is useful for
    | catching missing or misnamed components.
    |
    | The `paths` and `extensions` options define where to look for page
    | components and which file extensions to consider.
    |
    */

    'pages' => [

        'ensure_pages_exist' => false,

        'paths' => [

            resource_path('js/pages'),

        ],

        'extensions' => [

            'js',
            'jsx',
            'svelte',
            'ts',
            'tsx',
            'vue',

        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Expose Shared Prop Keys
    |--------------------------------------------------------------------------
    |
    | When enabled, each page response includes a `sharedProps` metadata key
    | listing the top-level prop keys that were registered via `Luna::share`.
    | The frontend can use this to carry shared props over during instant visits.
    |
    */

    'expose_shared_prop_keys' => true,

    /*
    |--------------------------------------------------------------------------
    | History
    |--------------------------------------------------------------------------
    |
    | Enable `encrypt` to encrypt page data before it is stored in the
    | browser's history state, preventing sensitive information from
    | being accessible after logout. Can also be enabled per-request
    | or via the `luna.encrypt` middleware.
    |
    */

    'history' => [

        'encrypt' => (bool) env('LUNA_ENCRYPT_HISTORY', false),

    ],

    /*
    |--------------------------------------------------------------------------
    | Telegram Mini App
    |--------------------------------------------------------------------------
    |
    | Configuration for building Telegram Mini Apps (TMA) with Luna. The bot
    | token is used to validate the init data every Mini App request carries.
    | Validation runs server-side and fails closed; never trust the client.
    |
    */

    'telegram' => [

        /*
        | The bot token used to validate init data via HMAC-SHA256. Keep this
        | server-side only; it must never reach the frontend bundle.
        */
        'bot_token' => env('BOT_TOKEN'),

        /*
        | Master switch for init-data validation. Only disable this in local
        | testing; disabling it in production defeats all Mini App security.
        */
        'validate' => (bool) env('LUNA_TG_VALIDATE', true),

        /*
        | The validation scheme:
        |   'hmac'      - HMAC-SHA256 keyed by the bot token (default).
        |   'signature' - Ed25519 signature against Telegram's public key.
        |   'both'      - require both to pass.
        */
        'scheme' => env('LUNA_TG_SCHEME', 'hmac'),

        /*
        | Maximum age of init data in seconds before it is treated as expired.
        | Guards against replay of captured init data. Set to 0 to disable.
        */
        'auth_ttl' => (int) env('LUNA_TG_AUTH_TTL', 86400),

        /*
        | The request header that carries the init data on Mini App visits.
        */
        'init_data_header' => env('LUNA_TG_INIT_DATA_HEADER', 'X-Telegram-Init-Data'),

        /*
        | Hex-encoded Ed25519 public keys published by Telegram, used for the
        | 'signature' scheme. The correct key is chosen by the 'test_env' flag.
        */
        'public_keys' => [
            'production' => 'e7bf03a2fa4602af4580703d88dda5bb59f32ed8b02a56c187fe7d34caed242d',
            'test' => '40055058a4ee38156a06562e52eece92a771bcb8dc3fded71d7cc3fb2c31d6b',
        ],

        /*
        | Whether the Mini App runs against Telegram's test environment. When
        | true, the 'test' public key is used for signature validation.
        */
        'test_env' => (bool) env('LUNA_TG_TEST_ENV', false),

        /*
        | Automatically share the validated Telegram context (user, chat, start
        | param, chat type/instance) as a `telegram` prop on every Luna response
        | once a request is authenticated. The one-time query id is never shared.
        */
        'share_props' => (bool) env('LUNA_TG_SHARE_PROPS', true),

        /*
        | The Telegram Mini App SDK script injected by the @telegramWebApp Blade
        | directive. Loading it from Telegram's CDN is required for the SDK to
        | function inside the Telegram client.
        */
        'sdk_url' => env('LUNA_TG_SDK_URL', 'https://telegram.org/js/telegram-web-app.js'),

    ],

];
