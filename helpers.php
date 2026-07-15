<?php

use LaraGram\Contracts\Support\Arrayable;
use LaraGram\Luna\Luna;
use LaraGram\Luna\Response;
use LaraGram\Luna\ResponseFactory;
use LaraGram\Luna\Telegram\TelegramContext;
use LaraGram\Luna\Telegram\WebAppUser;

if (! function_exists('luna')) {
    /**
     * Luna helper.
     *
     * @param  null|string  $component
     * @param  array|Arrayable  $props
     * @return ($component is null ? ResponseFactory : Response)
     */
    function luna($component = null, $props = [])
    {
        $instance = Luna::getFacadeRoot();

        if ($component) {
            return $instance->render($component, $props);
        }

        return $instance;
    }
}

if (! function_exists('luna_location')) {
    /**
     * Luna location helper.
     *
     * @param  string  url
     * @return \LaraGram\Http\BaseResponse
     */
    function luna_location($url)
    {
        $instance = Luna::getFacadeRoot();

        return $instance->location($url);
    }
}

if (! function_exists('telegram')) {
    /**
     * Get the Telegram Mini App context for the current request.
     *
     * @return TelegramContext
     */
    function telegram()
    {
        return app(TelegramContext::class);
    }
}

if (! function_exists('tg_user')) {
    /**
     * Get the validated Telegram Web App user for the current request.
     *
     * @return WebAppUser|null
     */
    function tg_user()
    {
        return app(TelegramContext::class)->user();
    }
}
