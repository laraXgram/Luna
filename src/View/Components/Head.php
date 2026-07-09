<?php

namespace LaraGram\Luna\View\Components;

use LaraGram\View\Component;
use LaraGram\Luna\Ssr\Response;
use LaraGram\Luna\Ssr\SsrState;

class Head extends Component
{
    public ?Response $response;

    public function __construct()
    {
        $this->response = app(SsrState::class)->dispatch();
    }

    public function render(): string
    {
        return <<<'blade'
@if($response)
{!! $response->head !!}
@else
{!! $slot !!}
@endif
blade;
    }
}
