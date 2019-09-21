<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\DataObject\Test;

use ArrayIterator;
use Czim\DataObject\Exceptions\UnassignableAttributeException;
use Illuminate\Contracts\Support\MessageBag;

class DataObjectTest extends TestCase
{
    /**
     * @test
     */
    function it_returns_null_for_unassigned_attributes()
    {
        $data = new Helpers\TestDataObject();

        static::assertNull($data->getAttribute('unset_key'), 'unassigned attribute was not null (getAttribute)');
        static::assertNull($data->unset_attribute, 'unassigned attribute was not null (magic)');
        static::assertNull($data['unset_array_key'], 'unassigned attribute was not null (array access)');
    }

    /**
     * @test
     */
    function it_stores_and_retrieves_attributes_individually()
    {
        // method assignment
        $data = new Helpers\TestDataObject();
        $data->setAttribute('name', 'some test value');
        static::assertEquals('some test value', $data->getAttribute('name'), 'method assignment failed');

        // magic assignment
        $data = new Helpers\TestDataObject();
        $data->name = 'some test value';
        static::assertEquals('some test value', $data->name, 'magic assignment failed');

        // array access
        $data = new Helpers\TestDataObject();
        $data['name'] = 'some test value';
        static::assertEquals('some test value', $data['name'], 'array assignment failed');
    }

    /**
     * @test
     */
    function it_handles_array_updates_by_reference()
    {
        $data = new Helpers\TestDataObject();

        $data->setAttribute('array', [ 'testing 0' ]);

        $data->array[] = 'testing 1';
        $data->array[] = 'testing 2';

        static::assertCount(3, $data->getAttribute('array'), 'array push failed, wrong count');
    }

    /**
     * @test
     */
    function it_mass_stores_and_retrieves_attributes()
    {
        $data = new Helpers\TestDataObject();

        $data->setAttributes([
            'mass'       => 'testing',
            'assignment' => 2242,
        ]);

        static::assertEquals('testing', $data->mass, 'mass assignment failed (1)');
        static::assertEquals(2242, $data->assignment, 'mass assignment failed (2)');
    }

    /**
     * @test
     */
    function it_initializes_attributes_through_its_constructor()
    {
        $data = new Helpers\TestDataObject([
            'mass'       => 'testing',
            'assignment' => 2242,
        ]);

        static::assertEquals('testing', $data->mass, 'constructor assignment failed (1)');
        static::assertEquals(2242, $data->assignment, 'constructor assignment failed (2)');
    }

    /**
     * @test
     */
    function it_validates_attributes()
    {
        $data = new Helpers\TestDataObject();

        // validate empty data against single required rule
        static::assertFalse($data->validate(), 'empty should not pass validation');

        $messages = $data->messages();
        static::assertInstanceOf(MessageBag::class, $messages, 'validation messages not of correct type');
        static::assertCount(1, $messages, 'validation messages should have 1 message');
        static::assertRegExp(
            '#name .*is required#i',
            $messages->first(),
            'validation message not as expected for empty data'
        );

        // validate partially incorrect data
        $data->name = 'Valid name';
        $data->list = 'not an array';

        static::assertFalse($data->validate(), 'incorrect data should not pass validation');

        $messages = $data->messages();
        static::assertCount(1, $messages, 'validation messages should have 1 message');
        static::assertRegexp(
            '#list .*must be an array#i',
            $messages->first(),
            'validation message not as expected for incorrect data'
        );

        // validate correct data should be okay
        $data->name = 'Valid name';
        $data->list = [ 'one' => 'present' ];

        static::assertTrue($data->validate(), 'Correct data should pass validation');
    }

    /**
     * @test
     */
    function it_returns_keys_for_set_attributes()
    {
        $data = new Helpers\TestDataObject();

        $data->key_set     = true;
        $data->another_key = 'okay';
        $data['last_key']  = 60;

        static::assertEquals(['key_set', 'another_key', 'last_key'], $data->getKeys());
    }

    /**
     * @test
     */
    function it_clears_all_attributes()
    {
        $data = new Helpers\TestDataObject();

        $data->key_set     = true;
        $data->another_key = 'okay';

        $data->clear();

        static::assertNull($data->key_set);
        static::assertNull($data['another_key']);
    }

    /**
     * @test
     */
    function it_performs_isset()
    {
        $data = new Helpers\TestDataObject();

        static::assertFalse(isset($data->key_name));

        $data->key_name = 'test';

        static::assertTrue(isset($data->key_name));
    }

    /**
     * @test
     */
    function it_performs_unset()
    {
        $data = new Helpers\TestDataObject();

        $data->key_name = 'test';

        unset($data->key_name);

        static::assertFalse(isset($data->key_name));

        $data->key_name = 'test';

        unset($data['key_name']);

        static::assertFalse(isset($data->key_name));
    }


    // ------------------------------------------------------------------------------
    //      Restrictive measures
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_throws_an_exception_when_assigning_to_disallowed_keys()
    {
        $this->expectException(UnassignableAttributeException::class);
        $this->expectExceptionMessageRegExp('#not allowed .*does_not_exist#i');

        $data = new Helpers\TestRestrictedDataObject();

        // allow normal assignment that is listed in $assignable
        $data->name = 'pietje';
        $data->setAttribute('list', [ 'some', 'list' ]);
        $data->setAttributes([
            'name' => 'pietje weer',
            'list' => [ 'other', 'items' ],
        ]);

        // exception on disallowed
        $data->does_not_exist = 'exception';
    }

    /**
     * @test
     * @depends it_throws_an_exception_when_assigning_to_disallowed_keys
     */
    function it_throws_an_exception_when_assigning_to_disallowed_keys_for_mass_assignment()
    {
        $this->expectException(UnassignableAttributeException::class);
        $this->expectExceptionMessageRegExp('#not allowed .*does_not_exist#i');

        $data = new Helpers\TestRestrictedDataObject();

        $data->setAttributes([
            'does_not_exist' => 'exception',
        ]);
    }

    /**
     * @test
     */
    function it_allows_setting_attributes_through_method_if_disallowing_assignment_by_magic()
    {
        $data = new Helpers\TestMagiclessDataObject();

        $data->setAttribute('name', 'okay');
        static::assertEquals('okay', $data->name, 'Should still allow normal assignment');
    }

    /**
     * @test
     */
    function it_throws_an_exception_when_assigning_by_magic_if_disallowed_entirely()
    {
        $this->expectException(UnassignableAttributeException::class);
        $this->expectExceptionMessageRegExp('#not allowed .*magic#i');

        $data = new Helpers\TestMagiclessDataObject();

        $data->magic_blows_up = 'fails';
    }

    /**
     * @test
     * @depends it_throws_an_exception_when_assigning_by_magic_if_disallowed_entirely
     */
    function it_throws_an_exception_when_assigning_by_array_access_if_disallowing_magic()
    {
        $this->expectException(UnassignableAttributeException::class);
        $this->expectExceptionMessageRegExp('#not allowed .*magic#i');

        $data = new Helpers\TestMagiclessDataObject();

        $data['array_access'] = 'fails as well';
    }


    // ------------------------------------------------------------------------------
    //      Array Access, Jsonable
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_is_arrayable()
    {
        $data = new Helpers\TestDataObject([
            'mass'       => 'testing',
            'assignment' => 2242,
        ]);

        $array = $data->toArray();

        static::assertIsArray($array, 'toArray() did not return array');
        static::assertCount(2, $array, 'incorrect item count');
        static::assertArrayHasKey('mass', $array);
        static::assertArrayHasKey('assignment', $array);
        static::assertEquals('testing', $array['mass']);
        static::assertEquals(2242, $array['assignment']);
    }

    /**
     * @test
     */
    function it_is_jsonable()
    {
        $data = new Helpers\TestDataObject([
            'mass'       => 'testing',
            'assignment' => 2242,
        ]);

        static::assertEquals('{"mass":"testing","assignment":2242}', $data->toJson(), 'incorrect toJson result');
    }

    /**
     * @test
     */
    function it_is_json_serializable()
    {
        $data = new Helpers\TestDataObject([
            'mass'       => 'testing',
            'assignment' => 2242,
        ]);

        static::assertEquals('{"mass":"testing","assignment":2242}', json_encode($data->jsonSerialize()), 'incorrect toJson result');
    }

    /**
     * @test
     */
    function it_outputs_json_when_cast_to_string()
    {
        $data = new Helpers\TestDataObject([
            'mass'       => 'testing',
            'assignment' => 2242,
        ]);

        $json = (string) $data;

        static::assertEquals('{"mass":"testing","assignment":2242}', $json, 'incorrect stringified result');
    }

    /**
     * @test
     */
    function it_is_convertable_to_an_object()
    {
        $data = new Helpers\TestDataObject([
            'mass'       => ['test' => true],
            'assignment' => 2242,
        ]);

        $object = $data->toObject();

        static::assertIsObject($object, 'not an object');
        static::assertEquals(2242, $object->assignment, 'incorrect direct property');
        static::assertIsObject($object->mass, 'nested array not an object');
        static::assertEquals(true, $object->mass->test, 'incorrect nested property (mass)');


        // Non-recursive
        $data = new Helpers\TestDataObject([
            'mass' => new Helpers\TestDataObject(['test' => true]),
        ]);

        $object = $data->toObject(false);

        static::assertIsObject($object, 'not an object');
        static::assertEquals(['test' => true], $object->mass);
    }

    /**
     * @test
     */
    function it_is_countable()
    {
        $data = new Helpers\TestDataObject([
            'one'   => 'testing',
            'two'   => 23,
            'three' => [ 'help', 'me', 'im', 'trapped', 'in', 'a', 'test', 'factory' ],
        ]);

        static::assertEquals(3, $data->count());
        static::assertCount(3, $data);
    }

    /**
     * @test
     */
    function it_recursively_deals_with_nested_arrayables()
    {
        $data = new Helpers\TestDataObject([
            'contents' => new Helpers\TestDataObject([
                'mass'       => 'testing',
                'assignment' => 2242,
            ]),
            'more' => [
                new Helpers\TestDataObject([ 'a' => 'b' ]),
            ],
        ]);

        $array = $data->toArray();

        static::assertIsArray($array, 'nested toArray() did not return array');
        static::assertCount(2, $array, 'incorrect item count');

        static::assertArrayHasKey('contents', $array);
        static::assertArrayHasKey('more', $array);
        static::assertArrayHasKey('mass', $array['contents']);
        static::assertArrayHasKey('assignment', $array['contents']);
        static::assertEquals('testing', $array['contents']['mass']);
        static::assertEquals(2242, $array['contents']['assignment']);
        static::assertEquals([['a' => 'b']], $array['more']);
    }


    // ------------------------------------------------------------------------------
    //      Dot Notation
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_takes_dot_notation_to_get_nested_data_at_depth()
    {
        $data = new Helpers\TestDataObject([
            'top' => 'test',
            'contents' => new Helpers\TestDataObject([
                'mass'       => 'testing',
                'assignment' => 2242,
            ]),
            'more' => [
                new Helpers\TestDataObject([ 'a' => 'b' ]),
            ],
            'array' => [ 'normal' => 'nested' ],
        ]);

        // If the method gets called with a null value, the object itself is returned
        static::assertSame($data, $data->getNested(null));

        static::assertEquals('test', $data->getNested('top'), 'Incorrect value for top level attribute');
        static::assertEquals('nested', $data->getNested('array.normal'), 'Incorrect value for nested array');
        static::assertEquals('testing', $data->getNested('contents.mass'), 'Incorrect value for recursive nested DataObject');
        static::assertEquals('b', $data->getNested('more.0.a'), 'Incorrect value for recursive nested DataObject in array');

        static::assertEquals('DEF', $data->getNested('more.1.4.3.hop', 'DEF'), 'Expecting default for wrong key');
    }

    /**
     * @test
     */
    function it_returns_an_iterator()
    {
        $data = new Helpers\TestDataObject(['a' => 1, 'b' => 2]);

        $iterator = $data->getIterator();

        static::assertInstanceOf(ArrayIterator::class, $iterator);
        static::assertCount(2, $iterator);
    }

}
