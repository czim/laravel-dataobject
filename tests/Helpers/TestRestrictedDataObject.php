<?php

namespace Czim\DataObject\Test\Helpers;

class TestRestrictedDataObject extends TestDataObject
{
    protected ?array $assignable = [
        'name',
        'list',
    ];
}
