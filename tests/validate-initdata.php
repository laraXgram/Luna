<?php

/**
 * Standalone verification for InitDataValidator (the repo ships no PHPUnit
 * harness). Builds signed init data with the official Telegram HMAC scheme and
 * asserts the validator accepts genuine data and rejects tampered / expired /
 * unsigned payloads.
 *
 * Run:  php tests/validate-initdata.php
 */

declare(strict_types=1);

// Minimal stub for the framework contract the value objects implement, so the
// classes load without the full LaraGram autoloader.
if (! interface_exists('LaraGram\\Contracts\\Support\\Arrayable')) {
    eval('namespace LaraGram\\Contracts\\Support; interface Arrayable { public function toArray(): array; }');
}

$src = __DIR__ . '/../src/Telegram';
require $src . '/Exceptions/InvalidInitDataException.php';
require $src . '/Exceptions/ExpiredInitDataException.php';
require $src . '/WebAppUser.php';
require $src . '/WebAppChat.php';
require $src . '/InitData.php';
require $src . '/InitDataValidator.php';

use LaraGram\Luna\Telegram\Exceptions\ExpiredInitDataException;
use LaraGram\Luna\Telegram\Exceptions\InvalidInitDataException;
use LaraGram\Luna\Telegram\InitDataValidator;

$failures = 0;

function check(string $label, bool $ok): void
{
    global $failures;
    echo ($ok ? "  PASS  " : "  FAIL  ") . $label . PHP_EOL;
    if (! $ok) {
        $failures++;
    }
}

/**
 * Build a valid HMAC-signed init-data query string for the given bot token.
 *
 * @param  array<string, string>  $fields  Decoded field values (no hash).
 */
function signInitData(string $botToken, array $fields): string
{
    $check = $fields;
    ksort($check);
    $pairs = [];
    foreach ($check as $key => $value) {
        $pairs[] = $key . '=' . $value;
    }
    $dataCheckString = implode("\n", $pairs);

    $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
    $hash = hash_hmac('sha256', $dataCheckString, $secretKey);

    $query = [];
    foreach ($fields as $key => $value) {
        $query[] = $key . '=' . rawurlencode($value);
    }
    $query[] = 'hash=' . $hash;

    return implode('&', $query);
}

$botToken = '7123456789:AA-testtoken-not-real';
$now = time();

$fields = [
    'auth_date' => (string) $now,
    'query_id' => 'AAHtest123',
    'user' => '{"id":42,"first_name":"Ada","username":"ada"}',
];

$validator = new InitDataValidator(botToken: $botToken, publicKey: null, ttl: 86400, scheme: 'hmac');

// 1. Genuine data validates and parses.
$raw = signInitData($botToken, $fields);
$initData = $validator->validate($raw);
check('valid init data accepted', true);
check('user id parsed', $initData->user?->id === 42);
check('username parsed', $initData->user?->username === 'ada');
check('query id parsed', $initData->queryId === 'AAHtest123');

// 1b. Current clients add an Ed25519 `signature`; the bot-token hash covers it too.
$signedRaw = signInitData($botToken, $fields + ['signature' => 'c2lnbmF0dXJlLWZyb20tdGVsZWdyYW0']);
check('init data carrying a signature accepted', $validator->tryValidate($signedRaw) !== null);

// 2. Tampered user field is rejected.
$tampered = str_replace('%22id%22%3A42', '%22id%22%3A99', $raw);
try {
    $validator->validate($tampered);
    check('tampered data rejected', false);
} catch (InvalidInitDataException) {
    check('tampered data rejected', true);
}

// 3. Wrong bot token is rejected (hash computed with another key).
$foreign = signInitData('9999999999:OTHER-token', $fields);
try {
    $validator->validate($foreign);
    check('foreign-signed data rejected', false);
} catch (InvalidInitDataException) {
    check('foreign-signed data rejected', true);
}

// 4. Missing hash is rejected.
try {
    $validator->validate('auth_date=' . $now . '&user=%7B%7D');
    check('missing hash rejected', false);
} catch (InvalidInitDataException) {
    check('missing hash rejected', true);
}

// 5. Expired data is rejected under a short TTL.
$staleFields = $fields;
$staleFields['auth_date'] = (string) ($now - 100000);
$staleRaw = signInitData($botToken, $staleFields);
$shortTtl = new InitDataValidator(botToken: $botToken, publicKey: null, ttl: 3600, scheme: 'hmac');
try {
    $shortTtl->validate($staleRaw);
    check('expired data rejected', false);
} catch (ExpiredInitDataException) {
    check('expired data rejected', true);
}

// 6. tryValidate returns null instead of throwing.
check('tryValidate returns null on tamper', $validator->tryValidate($tampered) === null);
check('tryValidate returns InitData on valid', $validator->tryValidate($raw) !== null);

echo PHP_EOL;
if ($failures === 0) {
    echo "All checks passed." . PHP_EOL;
    exit(0);
}

echo $failures . " check(s) FAILED." . PHP_EOL;
exit(1);
