<?php

namespace LaraGram\Luna\Telegram;

use LaraGram\Support\Facades\Request as BotRequest;
use RuntimeException;

/**
 * Server-side Bot API actions a Mini App backend commonly triggers, wrapped so
 * the query id / user id default to the current validated Telegram context.
 *
 * Exposed as `Luna::shareMessage()`, `Luna::invoiceLink()`,
 * `Luna::setMenuButton()` and `Luna::miniAppMenuButton()` macros.
 */
class BotActions
{
    public function __construct(
        protected TelegramContext $context,
    ) {
    }

    /**
     * Prepare an inline message the user can share from the Mini App via the
     * client `share.message(id)` helper. Defaults the target user to the
     * current Telegram context.
     *
     * @param  array<string, mixed>|object  $result  An InlineQueryResult.
     * @param  array<string, bool>  $options  allow_user_chats / allow_bot_chats / allow_group_chats / allow_channel_chats.
     * @return mixed The Bot API PreparedInlineMessage (contains the `id`).
     */
    public function shareMessage(array|object $result, ?int $userId = null, array $options = []): mixed
    {
        $userId ??= $this->context->user()?->id;

        if ($userId === null) {
            throw new RuntimeException('No Telegram user id available to prepare an inline message.');
        }

        return BotRequest::savePreparedInlineMessage(
            $userId,
            $result,
            $options['allow_user_chats'] ?? null,
            $options['allow_bot_chats'] ?? null,
            $options['allow_group_chats'] ?? null,
            $options['allow_channel_chats'] ?? null,
        );
    }

    /**
     * Create an invoice link for `client.openInvoice(url)`. Pass Bot API
     * `createInvoiceLink` fields by name (title, description, payload, currency,
     * prices, and any optional fields).
     *
     * @param  array<string, mixed>  $params
     * @return mixed The invoice link URL.
     */
    public function invoiceLink(array $params): mixed
    {
        foreach (['title', 'description', 'payload', 'currency', 'prices'] as $required) {
            if (! array_key_exists($required, $params)) {
                throw new RuntimeException("Missing required invoice field: {$required}.");
            }
        }

        return BotRequest::createInvoiceLink(...$params);
    }

    /**
     * Set the chat menu button. Defaults to the bot's default menu button when
     * no chat id is given.
     *
     * @param  array<string, mixed>|object  $menuButton
     */
    public function setMenuButton(array|object $menuButton, ?int $chatId = null): mixed
    {
        return BotRequest::setChatMenuButton($chatId, $menuButton);
    }

    /**
     * Convenience: set a `web_app` menu button that launches this Mini App.
     */
    public function miniAppMenuButton(string $text, string $url, ?int $chatId = null): mixed
    {
        return $this->setMenuButton([
            'type' => 'web_app',
            'text' => $text,
            'web_app' => ['url' => $url],
        ], $chatId);
    }
}
