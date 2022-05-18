<?php

namespace Czim\DataObject\Test\Helpers;

class TestNotADataObject
{
    private array $attributes = [];

    public function __construct($attributes)
    {
        $this->attributes = $attributes;
    }
}
