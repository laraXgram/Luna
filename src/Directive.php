<?php

namespace LaraGram\Luna;

class Directive
{
    /**
     * Compile the "@luna" Blade directive. This directive renders the
     * Luna root element with the page data, handling both client-side
     * rendering and SSR fallback scenarios.
     *
     * @param  string  $expression
     */
    public static function compile($expression = ''): string
    {
        $id = trim(trim($expression), "\'\"") ?: 'app';

        $template = '<?php
            $__lunaSsrResponse = app(\LaraGram\Luna\Ssr\SsrState::class)->setPage($page)->dispatch();

            if ($__lunaSsrResponse) {
                echo $__lunaSsrResponse->body;
            } else {
                ?><script data-page="'.$id.'" type="application/json">{!! json_encode($page) !!}</script><div id="'.$id.'"></div><?php
            }
        ?>';

        return implode(' ', array_map('trim', explode("\n", $template)));
    }

    /**
     * Compile the "@lunaHead" Blade directive. This directive renders the
     * head content for SSR responses, including meta tags, title, and other
     * head elements from the server-side render.
     *
     * @param  string  $expression
     */
    public static function compileHead($expression = ''): string
    {
        $template = '<?php
            $__lunaSsrResponse = app(\LaraGram\Luna\Ssr\SsrState::class)->setPage($page)->dispatch();

            if ($__lunaSsrResponse) {
                echo $__lunaSsrResponse->head;
            }
        ?>';

        return implode(' ', array_map('trim', explode("\n", $template)));
    }
}
