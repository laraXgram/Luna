<?php

namespace LaraGram\Luna;

use Closure;
use LaraGram\Http\Request;
use LaraGram\Http\BaseResponse as Response;

class EncryptHistoryMiddleware
{
    /**
     * Handle the incoming request and enable history encryption. This middleware
     * enables encryption of the browser history state, providing additional
     * security for sensitive data in Luna responses.
     *
     * @return Response
     */
    public function handle(Request $request, Closure $next)
    {
        Luna::encryptHistory();

        return $next($request);
    }
}
