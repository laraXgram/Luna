<?php

namespace LaraGram\Luna\Telegram;

/**
 * Request-scoped holder for the validated Telegram init data.
 *
 * The {@see \LaraGram\Luna\Middleware\AuthenticateTelegram} middleware populates
 * this once per request. Everything downstream; guards, controllers, shared
 * props; reads the trusted context from here instead of re-parsing headers.
 */
class TelegramContext
{
    /**
     * The validated init data for the current request, if any.
     */
    protected ?InitData $initData = null;

    /**
     * Store the validated init data for the current request.
     */
    public function setInitData(InitData $initData): void
    {
        $this->initData = $initData;
    }

    /**
     * Determine whether the current request carries validated init data.
     */
    public function has(): bool
    {
        return $this->initData !== null;
    }

    /**
     * Get the validated init data, or null when the request is not a Mini App request.
     */
    public function initData(): ?InitData
    {
        return $this->initData;
    }

    /**
     * Get the validated Telegram user for the current request.
     */
    public function user(): ?WebAppUser
    {
        return $this->initData?->user;
    }

    /**
     * Get the validated Telegram chat for the current request.
     */
    public function chat(): ?WebAppChat
    {
        return $this->initData?->chat;
    }

    /**
     * Get the query id required to answer the Web App query.
     */
    public function queryId(): ?string
    {
        return $this->initData?->queryId;
    }

    /**
     * Get the deep-link start parameter, if present.
     */
    public function startParam(): ?string
    {
        return $this->initData?->startParam;
    }

    /**
     * Clear the stored context.
     */
    public function flush(): void
    {
        $this->initData = null;
    }
}
