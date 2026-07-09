<?php

namespace LaraGram\Luna;

use LaraGram\Http\Request;

class Controller
{
    /**
     * Handle the incoming request and render the Luna response.
     * Renders the component and props defined in the route defaults.
     */
    public function __invoke(Request $request): Response
    {
        return Luna::render(
            $request->route()->defaults['component'],
            $request->route()->defaults['props']
        );
    }
}
