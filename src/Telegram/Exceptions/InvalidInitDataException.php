<?php

namespace LaraGram\Luna\Telegram\Exceptions;

use RuntimeException;

/**
 * Thrown when Telegram init data fails cryptographic validation.
 *
 * This covers a malformed payload, a hash mismatch, or an invalid Ed25519
 * signature. It must always result in the request being rejected.
 */
class InvalidInitDataException extends RuntimeException
{
    public static function malformed(): self
    {
        return new self('The Telegram init data is malformed.');
    }

    public static function missingHash(): self
    {
        return new self('The Telegram init data is missing its hash.');
    }

    public static function hashMismatch(): self
    {
        return new self('The Telegram init data hash does not match.');
    }

    public static function missingSignature(): self
    {
        return new self('The Telegram init data is missing its signature.');
    }

    public static function signatureMismatch(): self
    {
        return new self('The Telegram init data signature is invalid.');
    }

    public static function missingBotToken(): self
    {
        return new self('No Telegram bot token is configured for init-data validation.');
    }

    public static function missingPublicKey(): self
    {
        return new self('No Telegram Ed25519 public key is configured for signature validation.');
    }
}
