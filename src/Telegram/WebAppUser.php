<?php

namespace LaraGram\Luna\Telegram;

use LaraGram\Contracts\Support\Arrayable;

/**
 * Immutable representation of a Telegram Web App user.
 *
 * Mirrors the `WebAppUser` object described in the Telegram Mini Apps
 * documentation. Instances are only ever created from init data that has
 * already passed cryptographic validation, so the values can be trusted.
 *
 * @see https://core.telegram.org/bots/webapps#webappuser
 *
 * @implements Arrayable<string, mixed>
 */
class WebAppUser implements Arrayable
{
    /**
     * @param  array<string, mixed>  $raw  The original, decoded user payload.
     */
    public function __construct(
        public readonly int $id,
        public readonly string $firstName,
        public readonly ?string $lastName = null,
        public readonly ?string $username = null,
        public readonly ?string $languageCode = null,
        public readonly bool $isPremium = false,
        public readonly bool $isBot = false,
        public readonly bool $addedToAttachmentMenu = false,
        public readonly bool $allowsWriteToPm = false,
        public readonly ?string $photoUrl = null,
        public readonly array $raw = [],
    ) {
    }

    /**
     * Create a user instance from a decoded init-data payload.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            firstName: (string) ($data['first_name'] ?? ''),
            lastName: isset($data['last_name']) ? (string) $data['last_name'] : null,
            username: isset($data['username']) ? (string) $data['username'] : null,
            languageCode: isset($data['language_code']) ? (string) $data['language_code'] : null,
            isPremium: (bool) ($data['is_premium'] ?? false),
            isBot: (bool) ($data['is_bot'] ?? false),
            addedToAttachmentMenu: (bool) ($data['added_to_attachment_menu'] ?? false),
            allowsWriteToPm: (bool) ($data['allows_write_to_pm'] ?? false),
            photoUrl: isset($data['photo_url']) ? (string) $data['photo_url'] : null,
            raw: $data,
        );
    }

    /**
     * Get the user's full display name.
     */
    public function fullName(): string
    {
        return trim($this->firstName.' '.($this->lastName ?? ''));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'username' => $this->username,
            'language_code' => $this->languageCode,
            'is_premium' => $this->isPremium,
            'is_bot' => $this->isBot,
            'added_to_attachment_menu' => $this->addedToAttachmentMenu,
            'allows_write_to_pm' => $this->allowsWriteToPm,
            'photo_url' => $this->photoUrl,
        ];
    }
}
