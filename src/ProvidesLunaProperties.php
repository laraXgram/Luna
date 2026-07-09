<?php

namespace LaraGram\Luna;

interface ProvidesLunaProperties
{
    /**
     * Get the properties to be provided to Luna. This method allows objects
     * to dynamically provide properties that will be serialized and sent
     * to the frontend.
     *
     * @return iterable<string, mixed>
     */
    public function toLunaProperties(RenderContext $context): iterable;
}
