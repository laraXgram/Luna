<?php

namespace LaraGram\Luna\Telegram;

use LaraGram\Luna\Telegram\Exceptions\ExpiredInitDataException;
use LaraGram\Luna\Telegram\Exceptions\InvalidInitDataException;

/**
 * Validates Telegram Web App init data.
 *
 * Two independent schemes are supported, matching the official Telegram
 * documentation:
 *
 *  - "hmac":      HMAC-SHA256 keyed by the bot token. Only the bot's own
 *                 backend can perform this check.
 *  - "signature": Ed25519 signature verified against Telegram's public key.
 *                 Allows a third party to validate without the bot token.
 *
 * All comparisons are constant-time and the validator fails closed: any
 * malformed, tampered, or stale payload raises an exception.
 *
 * @see https://core.telegram.org/bots/webapps#validating-data-received-via-the-mini-app
 */
class InitDataValidator
{
    /**
     * The label Telegram mixes into the HMAC key derivation.
     */
    protected const HMAC_KEY = 'WebAppData';

    /**
     * @param  string|null  $botToken   The bot token used for HMAC validation.
     * @param  string|null  $publicKey  Hex-encoded Ed25519 public key for signature validation.
     * @param  int          $ttl        Maximum age of the init data in seconds (0 disables the check).
     * @param  string       $scheme     One of "hmac", "signature", or "both".
     */
    public function __construct(
        protected readonly ?string $botToken = null,
        protected readonly ?string $publicKey = null,
        protected readonly int $ttl = 86400,
        protected readonly string $scheme = 'hmac',
    ) {
    }

    /**
     * Validate a raw init-data query string and return the trusted payload.
     *
     * @throws InvalidInitDataException
     * @throws ExpiredInitDataException
     */
    public function validate(string $initData): InitData
    {
        $fields = $this->parse($initData);

        if ($fields === []) {
            throw InvalidInitDataException::malformed();
        }

        if (! isset($fields['hash']) || $fields['hash'] === '') {
            throw InvalidInitDataException::missingHash();
        }

        if (in_array($this->scheme, ['hmac', 'both'], true)) {
            $this->verifyHmac($fields);
        }

        if (in_array($this->scheme, ['signature', 'both'], true)) {
            $this->verifySignature($fields);
        }

        $this->ensureFresh($fields);

        return InitData::fromFields($fields, $initData);
    }

    /**
     * Validate init data without throwing, returning null on any failure.
     */
    public function tryValidate(string $initData): ?InitData
    {
        try {
            return $this->validate($initData);
        } catch (InvalidInitDataException|ExpiredInitDataException) {
            return null;
        }
    }

    /**
     * Verify the HMAC-SHA256 hash keyed by the bot token.
     *
     * @param  array<string, string>  $fields
     *
     * @throws InvalidInitDataException
     */
    protected function verifyHmac(array $fields): void
    {
        if (empty($this->botToken)) {
            throw InvalidInitDataException::missingBotToken();
        }

        $dataCheckString = $this->dataCheckString($fields);

        $secretKey = hash_hmac('sha256', $this->botToken, self::HMAC_KEY, true);
        $expected = hash_hmac('sha256', $dataCheckString, $secretKey);

        if (! hash_equals($expected, (string) $fields['hash'])) {
            throw InvalidInitDataException::hashMismatch();
        }
    }

    /**
     * Verify the Ed25519 signature against Telegram's public key.
     *
     * @param  array<string, string>  $fields
     *
     * @throws InvalidInitDataException
     */
    protected function verifySignature(array $fields): void
    {
        if (empty($fields['signature'])) {
            throw InvalidInitDataException::missingSignature();
        }

        if (empty($this->publicKey)) {
            throw InvalidInitDataException::missingPublicKey();
        }

        $botId = $this->botId();
        $dataCheckString = $botId.':'.self::HMAC_KEY."\n".$this->dataCheckString($fields);

        $signature = $this->base64UrlDecode((string) $fields['signature']);
        $publicKey = @hex2bin($this->publicKey);

        if ($signature === false || $publicKey === false || strlen($publicKey) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            throw InvalidInitDataException::signatureMismatch();
        }

        $valid = sodium_crypto_sign_verify_detached($signature, $dataCheckString, $publicKey);

        if (! $valid) {
            throw InvalidInitDataException::signatureMismatch();
        }
    }

    /**
     * Ensure the init data is not older than the configured TTL.
     *
     * @param  array<string, string>  $fields
     *
     * @throws ExpiredInitDataException
     */
    protected function ensureFresh(array $fields): void
    {
        if ($this->ttl <= 0) {
            return;
        }

        $authDate = (int) ($fields['auth_date'] ?? 0);

        if ($authDate <= 0 || (time() - $authDate) > $this->ttl) {
            throw ExpiredInitDataException::make($authDate, $this->ttl);
        }
    }

    /**
     * Build the data-check-string: every field except `hash` and `signature`,
     * sorted by key, formatted as `key=value` and joined with line feeds.
     *
     * @param  array<string, string>  $fields
     */
    protected function dataCheckString(array $fields): string
    {
        unset($fields['hash'], $fields['signature']);

        ksort($fields);

        $pairs = [];

        foreach ($fields as $key => $value) {
            $pairs[] = $key.'='.$value;
        }

        return implode("\n", $pairs);
    }

    /**
     * Parse a raw init-data query string into decoded key/value pairs.
     *
     * A manual parser is used instead of parse_str() to avoid PHP mangling
     * keys and to keep values byte-exact for the hash computation.
     *
     * @return array<string, string>
     */
    protected function parse(string $initData): array
    {
        $fields = [];

        foreach (explode('&', $initData) as $chunk) {
            if ($chunk === '') {
                continue;
            }

            $parts = explode('=', $chunk, 2);

            if (count($parts) !== 2) {
                continue;
            }

            $fields[urldecode($parts[0])] = urldecode($parts[1]);
        }

        return $fields;
    }

    /**
     * Extract the numeric bot id from the configured bot token.
     *
     * @throws InvalidInitDataException
     */
    protected function botId(): string
    {
        if (! empty($this->botToken) && str_contains($this->botToken, ':')) {
            return explode(':', $this->botToken, 2)[0];
        }

        throw InvalidInitDataException::missingBotToken();
    }

    /**
     * Decode a base64url string, tolerating missing padding.
     *
     * @return string|false
     */
    protected function base64UrlDecode(string $value): string|false
    {
        $value = strtr($value, '-_', '+/');
        $padding = strlen($value) % 4;

        if ($padding > 0) {
            $value .= str_repeat('=', 4 - $padding);
        }

        return base64_decode($value, true);
    }
}
