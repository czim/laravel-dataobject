<?php
namespace Czim\DataObject\Test\Helpers;

class TestNotADataObject
{
    private $attributes;

    public function __construct($attributes)
    {
        $this->attributes = $attributes;
    }
}
