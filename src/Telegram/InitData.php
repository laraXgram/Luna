<?php

namespace LaraGram\Luna\Telegram;

use LaraGram\Contracts\Support\Arrayable;

/**
 * Immutable, validated representation of Telegram Web App init data.
 *
 * An instance of this class only exists once the raw init-data string has
 * passed {@see InitDataValidator}. Every field here can therefore be trusted.
 *
 * @see https://core.telegram.org/bots/webapps#webappinitdata
 *
 * @implements Arrayable<string, mixed>
 */
class InitData implements Arrayable
{
    /**
     * @param  array<string, string>  $fields  The raw decoded query fields.
     */
    public function __construct(
        public readonly ?WebAppUser $user,
        public readonly ?WebAppChat $chat,
        public readonly ?WebAppUser $receiver,
        public readonly ?string $queryId,
        public readonly ?string $startParam,
        public readonly ?string $chatType,
        public readonly ?string $chatInstance,
        public readonly ?int $canSendAfter,
        public readonly int $authDate,
        public readonly string $hash,
        public readonly ?string $signature,
        public readonly string $raw,
        public readonly array $fields = [],
    ) {
    }

    /**
     * Build an instance from already-decoded init-data fields.
     *
     * Note: this performs no validation. Only call it from within the
     * validator, after the hash/signature has been verified.
     *
     * @param  array<string, string>  $fields
     */
    public static function fromFields(array $fields, string $raw): self
    {
        $decode = static function (string $key) use ($fields): ?array {
            if (! isset($fields[$key])) {
                return null;
            }

            $decoded = json_decode($fields[$key], true);

            return is_array($decoded) ? $decoded : null;
        };

        $user = $decode('user');
        $chat = $decode('chat');
        $receiver = $decode('receiver');

        return new self(
            user: $user ? WebAppUser::fromArray($user) : null,
            chat: $chat ? WebAppChat::fromArray($chat) : null,
            receiver: $receiver ? WebAppUser::fromArray($receiver) : null,
            queryId: $fields['query_id'] ?? null,
            startParam: $fields['start_param'] ?? null,
            chatType: $fields['chat_type'] ?? null,
            chatInstance: $fields['chat_instance'] ?? null,
            canSendAfter: isset($fields['can_send_after']) ? (int) $fields['can_send_after'] : null,
            authDate: (int) ($fields['auth_date'] ?? 0),
            hash: $fields['hash'] ?? '',
            signature: $fields['signature'] ?? null,
            raw: $raw,
            fields: $fields,
        );
    }

    /**
     * Get the moment the init data was issued.
     */
    public function authDateTimestamp(): int
    {
        return $this->authDate;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user' => $this->user?->toArray(),
            'chat' => $this->chat?->toArray(),
            'receiver' => $this->receiver?->toArray(),
            'query_id' => $this->queryId,
            'start_param' => $this->startParam,
            'chat_type' => $this->chatType,
            'chat_instance' => $this->chatInstance,
            'can_send_after' => $this->canSendAfter,
            'auth_date' => $this->authDate,
        ];
    }
}
