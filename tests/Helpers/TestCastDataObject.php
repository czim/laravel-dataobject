<?php

namespace Czim\DataObject\Test\Helpers;

use Czim\DataObject\CastableDataObject;

class TestCastDataObject extends CastableDataObject
{

    /**
     * Returns cast types per attribute key.
     *
     * @return array    associative
     */
    protected function casts(): array
    {
        return [
            'bool'    => 'boolean',
            'int'     => 'integer',
            'float'   => 'float',
            'string'  => 'string',
            'array'   => 'array',
            'object'  => TestDataObject::class,
            'objects' => TestDataObject::class . '[]',
        ];
    }

}
