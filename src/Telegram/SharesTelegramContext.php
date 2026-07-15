<?php

namespace LaraGram\Luna\Telegram;

use LaraGram\Luna\Luna;

/**
 * Shares the validated Telegram context as a `telegram` prop on every Luna
 * response, so client components can read the trusted user / chat without a
 * round-trip. The one-time `query_id` is deliberately excluded; it stays
 * server-side and is consumed via {@see \LaraGram\Luna\ResponseFactory::answerQuery()}.
 */
trait SharesTelegramContext
{
    /**
     * Share the validated init data as Luna props.
     */
    protected function shareTelegramContext(InitData $initData): void
    {
        if (! config('luna.telegram.share_props', true)) {
            return;
        }

        Luna::share('telegram', [
            'user' => $initData->user?->toArray(),
            'chat' => $initData->chat?->toArray(),
            'receiver' => $initData->receiver?->toArray(),
            'startParam' => $initData->startParam,
            'chatType' => $initData->chatType,
            'chatInstance' => $initData->chatInstance,
        ]);
    }
}
