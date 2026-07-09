<?php

namespace LaraGram\Luna\Ssr;

interface Gateway
{
    /**
     * Dispatch the Luna page to the SSR engine.
     *
     * @param  array<string, mixed>  $page
     */
    public function dispatch(array $page): ?Response;
}
