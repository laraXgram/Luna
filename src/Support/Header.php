<?php

namespace LaraGram\Luna\Support;

class Header
{
    /**
     * The main Luna request header.
     */
    public const LUNA = 'X-Luna';

    /**
     * Header for specifying which error bag to use for validation errors.
     */
    public const ERROR_BAG = 'X-Luna-Error-Bag';

    /**
     * Header for external redirects.
     */
    public const LOCATION = 'X-Luna-Location';

    /**
     * Header for hash fragment redirects.
     */
    public const REDIRECT = 'X-Luna-Redirect';

    /**
     * Header for the current asset version.
     */
    public const VERSION = 'X-Luna-Version';

    /**
     * Header specifying the component for partial reloads.
     */
    public const PARTIAL_COMPONENT = 'X-Luna-Partial-Component';

    /**
     * Header specifying which props to include in partial reloads.
     */
    public const PARTIAL_ONLY = 'X-Luna-Partial-Data';

    /**
     * Header specifying which props to exclude from partial reloads.
     */
    public const PARTIAL_EXCEPT = 'X-Luna-Partial-Except';

    /**
     * Header for resetting the page state.
     */
    public const RESET = 'X-Luna-Reset';

    /**
     * Header for specifying the merge intent when paginating on infinite scroll.
     */
    public const INFINITE_SCROLL_MERGE_INTENT = 'X-Luna-Infinite-Scroll-Merge-Intent';

    /**
     * Header specifying which once props to exclude from the response.
     */
    public const EXCEPT_ONCE_PROPS = 'X-Luna-Except-Once-Props';
}
