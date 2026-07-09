<?php

namespace LaraGram\Luna\View\Components;

use LaraGram\View\Component;
use LaraGram\Luna\Ssr\Response;
use LaraGram\Luna\Ssr\SsrState;

class App extends Component
{
    public ?Response $response;

    public string $pageJson;

    public function __construct(
        public string $id = 'app',
    ) {
        $state = app(SsrState::class);
        $this->response = $state->dispatch();
        $this->pageJson = json_encode($state->page);
    }

    public function render(): string
    {
        return <<<'blade'
@if($response)
{!! $response->body !!}
@else
<script data-page="{{ $id }}" type="application/json">{!! $pageJson !!}</script><div id="{{ $id }}"></div>
@endif
blade;
    }
}
