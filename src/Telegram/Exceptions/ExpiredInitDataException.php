<?php

namespace LaraGram\Luna\Telegram\Exceptions;

use RuntimeException;

/**
 * Thrown when Telegram init data is older than the configured TTL.
 *
 * Enforcing freshness on `auth_date` prevents captured init data from being
 * replayed indefinitely.
 */
class ExpiredInitDataException extends RuntimeException
{
    public static function make(int $authDate, int $ttl): self
    {
        return new self(sprintf(
            'The Telegram init data has expired (issued at %d, TTL %d seconds).',
            $authDate,
            $ttl
        ));
    }
}
