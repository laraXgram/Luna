<?php

namespace LaraGram\Luna\Telegram;

use LaraGram\Support\Facades\Facade;

/**
 * @method static void setInitData(\LaraGram\Luna\Telegram\InitData $initData)
 * @method static bool has()
 * @method static \LaraGram\Luna\Telegram\InitData|null initData()
 * @method static \LaraGram\Luna\Telegram\WebAppUser|null user()
 * @method static \LaraGram\Luna\Telegram\WebAppChat|null chat()
 * @method static string|null queryId()
 * @method static string|null startParam()
 * @method static void flush()
 *
 * @see \LaraGram\Luna\Telegram\TelegramContext
 */
class Telegram extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return TelegramContext::class;
    }
}
