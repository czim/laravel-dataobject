<?php
namespace Czim\DataObject\Test\Helpers;

use Czim\DataObject\CastableDataObject;

class TestCastDataObjectCastNull extends CastableDataObject
{

    /**
     * @var bool
     */
    protected $castUnsetObjects = true;

    /**
     * Returns cast types per attribute key.
     *
     * @return array    associative
     */
    protected function casts()
    {
        return [
            'object' => TestDataObject::class,
        ];
    }

}
