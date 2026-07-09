<?php

namespace LaraGram\Luna;

interface ProvidesLunaProperty
{
    /**
     * Convert the instance to an Luna property value. This method is called
     * when the object is used as a property value in an Luna response,
     * allowing for custom serialization.
     */
    public function toLunaProperty(PropertyContext $prop): mixed;
}
