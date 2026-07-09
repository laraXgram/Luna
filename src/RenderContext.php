<?php

namespace LaraGram\Luna;

use LaraGram\Http\Request;

class RenderContext
{
    /**
     * Create a new render context instance. The render context provides
     * information about the current Luna render operation to objects
     * implementing ProvidesLunaProperties.
     */
    public function __construct(
        public string $component,
        public Request $request
    ) {
        //
    }
}
