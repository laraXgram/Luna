<?php

namespace LaraGram\Luna\Telegram\Exceptions;

use RuntimeException;

/**
 * Thrown when a request that requires Telegram init data does not carry any.
 */
class MissingInitDataException extends RuntimeException
{
    public static function make(): self
    {
        return new self('No Telegram init data was present on the request.');
    }
}
