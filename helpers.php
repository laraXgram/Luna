<?php

use LaraGram\Contracts\Support\Arrayable;
use LaraGram\Luna\Luna;
use LaraGram\Luna\Response;
use LaraGram\Luna\ResponseFactory;

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
