<?php

namespace LaraGram\Luna\Middleware;

use Closure;
use LaraGram\Http\Request;
use LaraGram\Http\BaseResponse as Response;
use LaraGram\Luna\Support\Header;
use LaraGram\Luna\Telegram\Exceptions\ExpiredInitDataException;
use LaraGram\Luna\Telegram\Exceptions\InvalidInitDataException;
use LaraGram\Luna\Telegram\InitData;
use LaraGram\Luna\Telegram\InitDataValidator;
use LaraGram\Luna\Telegram\SharesTelegramContext;
use LaraGram\Luna\Telegram\TelegramContext;

/**
 * Validates the Telegram init data on the incoming request and populates the
 * request-scoped {@see TelegramContext}.
 *
 * Register it with an optional mode:
 *
 *   'telegram'           : required: reject requests without valid init data.
 *   'telegram:optional'  : best effort: continue unauthenticated when absent
 *                          or invalid (still rejects tampered data).
 *
 * The middleware always fails closed: tampered or expired init data is
 * rejected in both modes.
 */
class AuthenticateTelegram
{
    use SharesTelegramContext;

    public function __construct(
        protected InitDataValidator $validator,
        protected TelegramContext $context,
    ) {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $mode = 'required'): Response
    {
        $optional = $mode === 'optional';

        $raw = $this->initDataFromRequest($request);

        if ($raw === null || $raw === '') {
            if ($optional) {
                return $next($request);
            }

            abort(401, 'Telegram init data is required.');
        }

        try {
            $initData = $this->validator->validate($raw);
        } catch (ExpiredInitDataException) {
            // Stale data is always rejected; never silently accepted.
            abort(401, 'Telegram init data has expired.');
        } catch (InvalidInitDataException) {
            // Tampered / malformed data is always rejected, even in optional mode.
            abort(403, 'Telegram init data is invalid.');
        }

        $this->bind($request, $initData);

        return $next($request);
    }

    /**
     * Bind the validated init data to the context and request.
     */
    protected function bind(Request $request, InitData $initData): void
    {
        $this->context->setInitData($initData);

        $request->attributes->set('telegram_init_data', $initData);

        $this->shareTelegramContext($initData);
    }

    /**
     * Resolve the raw init-data string from the request.
     *
     * Preference order: the dedicated header (Mini App XHR visits), then a
     * `tgWebAppData`/`_auth` query or body parameter (initial document load).
     */
    protected function initDataFromRequest(Request $request): ?string
    {
        $header = config('luna.telegram.init_data_header', Header::INIT_DATA);

        if ($value = $request->header($header)) {
            return $value;
        }

        foreach (['tgWebAppData', '_auth', 'initData'] as $key) {
            if ($value = $request->input($key)) {
                return is_string($value) ? $value : null;
            }
        }

        return null;
    }
}
