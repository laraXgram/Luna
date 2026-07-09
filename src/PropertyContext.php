<?php

namespace LaraGram\Luna;

use LaraGram\Http\Request;

class PropertyContext
{
    /**
     * Create a new property context instance. The property context provides
     * information about the current property being resolved to objects
     * implementing ProvidesLunaProperty.
     *
     * @param  array<string, mixed>  $props
     */
    public function __construct(
        public string $key,
        public array $props,
        public Request $request
    ) {
        //
    }
}
