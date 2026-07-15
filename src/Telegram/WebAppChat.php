<?php

namespace LaraGram\Luna\Telegram;

use LaraGram\Contracts\Support\Arrayable;

/**
 * Immutable representation of a Telegram Web App chat.
 *
 * Present when the Mini App is launched from the attachment menu in a group,
 * supergroup or channel.
 *
 * @see https://core.telegram.org/bots/webapps#webappchat
 *
 * @implements Arrayable<string, mixed>
 */
class WebAppChat implements Arrayable
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public readonly int $id,
        public readonly string $type,
        public readonly string $title,
        public readonly ?string $username = null,
        public readonly ?string $photoUrl = null,
        public readonly array $raw = [],
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            type: (string) ($data['type'] ?? ''),
            title: (string) ($data['title'] ?? ''),
            username: isset($data['username']) ? (string) $data['username'] : null,
            photoUrl: isset($data['photo_url']) ? (string) $data['photo_url'] : null,
            raw: $data,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'username' => $this->username,
            'photo_url' => $this->photoUrl,
        ];
    }
}
