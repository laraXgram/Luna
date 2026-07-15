<?php

namespace LaraGram\Luna\Auth;

use LaraGram\Auth\GuardHelpers;
use LaraGram\Contracts\Auth\Guard;
use LaraGram\Contracts\Auth\UserProvider;
use LaraGram\Luna\Telegram\TelegramContext;

/**
 * Authentication guard for Telegram Mini App (Web App) requests.
 *
 * Identity is derived from the cryptographically validated init data held by
 * the {@see TelegramContext}, then hydrated through the configured user
 * provider by its Telegram user identifier. Because init data is validated by
 * the middleware before this guard is ever consulted, resolving a user here is
 * safe: a returned user is an authenticated user.
 */
class TelegramWebAppGuard implements Guard
{
    use GuardHelpers;

    /**
     * Create a new Telegram Web App guard.
     */
    public function __construct(
        UserProvider $provider,
        protected TelegramContext $context,
    ) {
        $this->provider = $provider;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \LaraGram\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if (! is_null($this->user)) {
            return $this->user;
        }

        $telegramUser = $this->context->user();

        if (! is_null($telegramUser)) {
            $this->user = $this->provider->retrieveByUserId($telegramUser->id);
        }

        return $this->user;
    }

    /**
     * Validate a user's credentials.
     *
     * Web App authentication is identity-based (no password), so this confirms
     * that the given Telegram user id resolves to a known user.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        if (empty($credentials['id'] ?? null)) {
            return false;
        }

        return ! is_null($this->provider->retrieveByUserId($credentials['id']));
    }

    /**
     * Get the validated Telegram user for the current request.
     */
    public function telegramUser()
    {
        return $this->context->user();
    }
}
