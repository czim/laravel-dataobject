<?php

namespace Czim\DataObject\Test;

use Czim\DataObject\Test\Helpers\TestDataObject;

class CastableDataObjectTest extends TestCase
{
    /**
     * @test
     */
    function it_casts_an_attribute_as_a_boolean()
    {
        $data = new Helpers\TestCastDataObject;

        static::assertSame(false, $data->bool);

        $data->bool = 'boolean value';
        static::assertSame(true, $data->bool);
    }

    /**
     * @test
     */
    function it_casts_an_attribute_as_an_integer()
    {
        $data = new Helpers\TestCastDataObject;

        static::assertSame(0, $data->int);

        $data->int = 'integer value';
        static::assertSame(0, $data->int);

        $data->int = 40.0;
        static::assertSame(40, $data->int);
    }

    /**
     * @test
     */
    function it_casts_an_attribute_as_a_float()
    {
        $data = new Helpers\TestCastDataObject;

        static::assertSame(0.0, $data->float);

        $data->float = 'float value';
        static::assertSame(0.0, $data->float);

        $data->float = 40;
        static::assertSame(40.0, $data->float);
    }

    /**
     * @test
     */
    function it_casts_an_attribute_as_a_string()
    {
        $data = new Helpers\TestCastDataObject;

        static::assertSame('', $data->string);

        $data->string = 'string value';
        static::assertSame('string value', $data->string);

        $data->string = 40;
        static::assertSame('40', $data->string);
    }

    /**
     * @test
     */
    function it_casts_an_attribute_as_an_array()
    {
        $data = new Helpers\TestCastDataObject;

        static::assertSame([], $data->array);

        $data->array = ['array'];
        static::assertSame(['array'], $data->array);

        $data->array = 'string value';
        static::assertSame(['string value'], $data->array);

        $data->array = new TestDataObject(['test' => 'type']);
        static::assertSame(['test' => 'type'], $data->array);
    }

    /**
     * @test
     */
    function it_casts_an_attribute_as_a_data_object()
    {
        $data = new Helpers\TestCastDataObject;

        $data->object = [
            'test' => 'testing',
        ];

        $object = $data->object;

        static::assertInstanceOf(TestDataObject::class, $object);
        static::assertEquals('testing', $object->test);
    }

    /**
     * @test
     */
    function it_casts_an_attribute_as_a_list_of_data_objects()
    {
        $data = new Helpers\TestCastDataObject;

        static::assertEquals([], $data->objects);

        $data->objects = [
            ['test' => 'testing a'],
            ['test' => 'testing b'],
        ];

        $objects = $data->objects;

        static::assertIsArray($objects);
        static::assertCount(2, $objects);
        static::assertInstanceOf(TestDataObject::class, $objects[0]);
        static::assertInstanceOf(TestDataObject::class, $objects[1]);
        static::assertEquals('testing a', $objects[0]->test);
        static::assertEquals('testing b', $objects[1]->test);
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    function it_throws_an_exception_if_it_cannot_cast_to_an_object()
    {
        $data = new Helpers\TestCastDataObject;

        $data->object = 'not an array';

        $data->object;
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    function it_throws_an_exception_if_it_cannot_cast_to_an_object_in_a_list()
    {
        $data = new Helpers\TestCastDataObject;

        $data->objects = [ ['type' => 'an array'], 444];

        $data->objects;
    }

    /**
     * @test
     */
    function it_returns_null_for_an_unset_attribute_cast_as_an_object()
    {
        $data = new Helpers\TestCastDataObject;

        static::assertNull($data->object);
    }

    /**
     * @test
     */
    function it_returns_empty_objects_for_an_unset_attribute_cast_as_an_object_if_configured_to()
    {
        $data = new Helpers\TestCastDataObjectCastNull;

        static::assertInstanceOf(TestDataObject::class, $data->object);
    }

    /**
     * @test
     */
    function it_returns_null_for_an_unset_attribute_in_an_array_cast_as_a_list_of_objects()
    {
        $data = new Helpers\TestCastDataObject;

        $data->objects = [
            null,
            ['type' => 'test'],
            null,
        ];

        $objects = $data->objects;

        static::assertIsArray($objects);
        static::assertCount(3, $objects);
        static::assertNull($objects[0]);
        static::assertNull($objects[2]);

        static::assertInstanceOf(TestDataObject::class, $objects[1]);
        static::assertEquals('test', $objects[1]->type);
    }

    /**
     * @test
     */
    function it_returns_empty_object_for_an_unset_attribute_in_an_array_cast_as_a_list_of_objects_if_configured_to()
    {
        $data = new Helpers\TestCastDataObjectCastNull;

        $data->objects = [
            null,
            ['type' => 'test'],
        ];

        $objects = $data->objects;

        static::assertIsArray($objects);
        static::assertCount(2, $objects);

        static::assertInstanceOf(TestDataObject::class, $objects[0]);
        static::assertNull($objects[0]->type);
        static::assertInstanceOf(TestDataObject::class, $objects[1]);
        static::assertEquals('test', $objects[1]->type);
    }

    /**
     * @test
     */
    function it_casts_attributes_on_toArray()
    {
        $data = new Helpers\TestCastDataObject;

        $data->int     = '6';
        $data->object  = new TestDataObject(['type' => 'object']);
        $data->objects = [
            ['test' => 'testing a'],
            ['test' => 'testing b'],
        ];

        $array = $data->toArray();

        static::assertIsArray($array);
        static::assertCount(7, $array);
        static::assertArrayHasKey('int', $array);
        static::assertSame(6, $array['int']);
        static::assertArrayHasKey('object', $array);
        static::assertEquals(['type' => 'object'], $array['object']);
        static::assertArrayHasKey('objects', $array);
        static::assertEquals([['test' => 'testing a'], ['test' => 'testing b']], $array['objects']);
        static::assertArrayHasKey('bool', $array);
        static::assertSame(false, $array['bool']);
        static::assertArrayHasKey('float', $array);
        static::assertSame(0.0, $array['float']);
        static::assertArrayHasKey('string', $array);
        static::assertSame('', $array['string']);
        static::assertArrayHasKey('array', $array);
        static::assertSame([], $array['array']);
    }

    // ------------------------------------------------------------------------------
    //      Use without casts
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_works_normally_without_casts_set()
    {
        $data = new Helpers\TestCastDataObjectWithoutCasts;

        $data->object = ['test' => 'testing'];
        $data->float = 'not a float';

        static::assertSame(['test' => 'testing'], $data->object);
        static::assertSame('not a float', $data['float']);

        $array = $data->toArray();
        static::assertCount(2, $array);
        static::assertSame(['test' => 'testing'], $array['object']);
        static::assertSame('not a float', $array['float']);
    }

}
