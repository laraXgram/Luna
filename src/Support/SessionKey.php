<?php

namespace LaraGram\Luna\Support;

class SessionKey
{
    /**
     * Session key for clearing the Luna history.
     */
    public const CLEAR_HISTORY = 'luna.clear_history';

    /**
     * Session key for flash data.
     */
    public const FLASH_DATA = 'luna.flash_data';

    /**
     * Session key for preserving the URL fragment.
     */
    public const PRESERVE_FRAGMENT = 'luna.preserve_fragment';
}
