<?php

namespace Czim\DataObject\Test\Helpers;

use Czim\DataObject\CastableDataObject;

class TestCastDataObjectCastNull extends CastableDataObject
{
    protected bool $castUnsetObjects = true;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'object'  => TestDataObject::class,
            'objects' => TestDataObject::class . '[]',
        ];
    }
}
