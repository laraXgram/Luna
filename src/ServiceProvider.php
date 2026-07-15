<?php

namespace LaraGram\Luna;

use LaraGram\Contracts\Http\Kernel as HttpKernelContract;
use LaraGram\Foundation\Http\Kernel;
use LaraGram\Http\RedirectResponse;
use LaraGram\Http\Request;
use LaraGram\Routing\Router;
use LaraGram\Support\Facades\Blade;
use LaraGram\Support\Facades\Request as BotRequest;
use LaraGram\Support\ServiceProvider as BaseServiceProvider;
use LaraGram\View\FileViewFinder;
use LaraGram\Luna\Auth\TelegramWebAppGuard;
use LaraGram\Luna\Ssr\Gateway;
use LaraGram\Luna\Ssr\HttpGateway;
use LaraGram\Luna\Ssr\SsrState;
use LaraGram\Luna\Support\Header;
use LaraGram\Luna\Telegram\BotActions;
use LaraGram\Luna\Telegram\InitDataValidator;
use LaraGram\Luna\Telegram\TelegramContext;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->singleton(HttpGateway::class);
        $this->app->singleton(ResponseFactory::class);
        $this->app->scoped(SsrState::class);
        $this->app->bind(Gateway::class, HttpGateway::class);

        $this->mergeConfigFrom(
            __DIR__ . '/../config/luna.php',
            'luna'
        );

        $this->registerBladeComponents();
        $this->registerBladeDirectives();
        $this->registerRedirectMacro();
        $this->registerRequestMacro();
        $this->registerRouterMacro();
        $this->registerMiddleware();
        $this->registerTelegram();

        $this->app->bind('luna.view-finder', function ($app) {
            return new FileViewFinder(
                $app['files'],
                $app['config']->get('luna.pages.paths'),
                $app['config']->get('luna.pages.extensions')
            );
        });
    }

    /**
     * Boot the service provider.
     */
    public function boot(): void
    {
        $this->registerConsoleCommands();
        $this->pushRedirectMiddleware();
        $this->registerTelegramGuard();

        $this->publishes([
            __DIR__ . '/../config/luna.php' => config_path('luna.php'),
        ]);
    }

    /**
     * Register the Telegram Mini App services: the request-scoped context and
     * the init-data validator built from the package configuration.
     */
    protected function registerTelegram(): void
    {
        $this->app->scoped(TelegramContext::class);

        $this->app->singleton(InitDataValidator::class, function ($app) {
            $config = $app['config']->get('luna.telegram', []);

            $keys = $config['public_keys'] ?? [];
            $publicKey = ($config['test_env'] ?? false)
                ? ($keys['test'] ?? null)
                : ($keys['production'] ?? null);

            return new InitDataValidator(
                botToken: $config['bot_token'] ?? null,
                publicKey: $publicKey,
                ttl: (int) ($config['auth_ttl'] ?? 86400),
                scheme: $config['scheme'] ?? 'hmac',
            );
        });

        $this->app->scoped(BotActions::class);

        $this->registerAnswerQueryMacro();
        $this->registerBotActionMacros();
    }

    /**
     * Register the bot-side action macros (`shareMessage`, `invoiceLink`,
     * `setMenuButton`, `miniAppMenuButton`) as thin delegates to
     * {@see BotActions}.
     */
    protected function registerBotActionMacros(): void
    {
        foreach (['shareMessage', 'invoiceLink', 'setMenuButton', 'miniAppMenuButton'] as $method) {
            ResponseFactory::macro($method, function (...$args) use ($method) {
                return app(BotActions::class)->{$method}(...$args);
            });
        }
    }

    /**
     * Register the `Luna::answerQuery()` macro, a thin wrapper over the Bot API
     * `answerWebAppQuery` that defaults the query id to the current request's
     * validated Telegram context.
     */
    protected function registerAnswerQueryMacro(): void
    {
        ResponseFactory::macro('answerQuery', function (array|object $result, ?string $queryId = null) {
            $queryId ??= app(TelegramContext::class)->queryId();

            if ($queryId === null || $queryId === '') {
                throw new \RuntimeException('No Telegram query id available to answer the Web App query.');
            }

            return BotRequest::answerWebAppQuery($queryId, $result);
        });
    }

    /**
     * Register the "telegram" authentication guard, resolving the app user
     * from the validated init data held by the request-scoped context.
     */
    protected function registerTelegramGuard(): void
    {
        $this->callAfterResolving('auth', function ($auth) {
            $auth->extend('telegram', function ($app, $name, array $config) use ($auth) {
                return new TelegramWebAppGuard(
                    $auth->createUserProvider($config['provider'] ?? null),
                    $app[TelegramContext::class],
                );
            });
        });
    }

    /**
     * Register the global redirect middleware for Luna requests.
     */
    protected function pushRedirectMiddleware(): void
    {
        $this->callAfterResolving(HttpKernelContract::class, function ($kernel) {
            if ($kernel instanceof Kernel) {
                $kernel->pushMiddleware(Middleware\EnsureGetOnRedirect::class);
            }
        });
    }

    /**
     * Register Blade components for rendering Luna head and body content.
     */
    protected function registerBladeComponents(): void
    {
        $this->callAfterResolving('blade.compiler', function () {
            Blade::componentNamespace('LaraGram\\Luna\\View\\Components', 'luna');
        });
    }

    /**
     * Register @luna and @lunaHead directives for rendering the Luna
     * root element and SSR head content in Blade templates.
     */
    protected function registerBladeDirectives(): void
    {
        $this->callAfterResolving('blade.compiler', function ($blade) {
            $blade->directive('luna', [Directive::class, 'compile']);
            $blade->directive('lunaHead', [Directive::class, 'compileHead']);
            $blade->directive('telegramWebApp', [Directive::class, 'compileTelegramWebApp']);
        });
    }

    /**
     * Register Artisan commands for managing Luna middleware creation
     * and server-side rendering operations when running in console mode.
     */
    protected function registerConsoleCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Commands\CreateMiddleware::class,
            Commands\StartSsr::class,
            Commands\StopSsr::class,
            Commands\CheckSsr::class,
        ]);
    }

    /**
     * Add a 'preserveFragment' method to redirect responses that signals
     * the frontend to preserve the URL fragment across the redirect.
     */
    protected function registerRedirectMacro(): void
    {
        RedirectResponse::macro('preserveFragment', function () {
            luna()->preserveFragment();

            return $this;
        });
    }

    /**
     * Add an 'luna' method to the Request class that returns true
     * if the current request is an Luna request.
     */
    protected function registerRequestMacro(): void
    {
        Request::macro('luna', function () {
            return (bool) $this->header(Header::LUNA);
        });
    }

    /**
     * Register the router macro.
     */
    protected function registerRouterMacro(): void
    {
        /**
         * @param  array<array-key, mixed>  $props
         */
        Router::macro('luna', function ($uri, $component, $props = []) {
            return $this->match(['GET', 'HEAD'], $uri, '\\'.Controller::class)
                ->defaults('component', $component)
                ->defaults('props', $props);
        });
    }

    /**
     * Register the middleware aliases.
     */
    protected function registerMiddleware(): void
    {
        $this->app['router']->aliasMiddleware(
            'luna.encrypt',
            EncryptHistoryMiddleware::class
        );

        $this->app['router']->aliasMiddleware(
            'telegram',
            Middleware\AuthenticateTelegram::class
        );
    }
}
