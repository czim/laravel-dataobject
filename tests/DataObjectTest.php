<?php
namespace Czim\DataObject\Test;

use Illuminate\Contracts\Support\MessageBag;

class DataObjectTest extends TestCase
{
    /**
     * @test
     */
    function it_returns_null_for_unassigned_attributes()
    {
        $data = new Helpers\TestDataObject();

        $this->assertNull($data->getAttribute('unset_key'), 'unassigned attribute was not null (getAttribute)');
        $this->assertNull($data->unset_attribute, 'unassigned attribute was not null (magic)');
        $this->assertNull($data['unset_array_key'], 'unassigned attribute was not null (array access)');
    }

    /**
     * @test
     */
    function it_stores_and_retrieves_attributes_individually()
    {
        // method assignment
        $data = new Helpers\TestDataObject();
        $data->setAttribute('name', 'some test value');
        $this->assertEquals('some test value', $data->getAttribute('name'), 'method assignment failed');

        // magic assignment
        $data = new Helpers\TestDataObject();
        $data->name = 'some test value';
        $this->assertEquals('some test value', $data->name, 'magic assignment failed');

        // array access
        $data = new Helpers\TestDataObject();
        $data['name'] = 'some test value';
        $this->assertEquals('some test value', $data['name'], 'array assignment failed');
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

        $this->assertCount(3, $data->getAttribute('array'), 'array push failed, wrong count');
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

        $this->assertEquals('testing', $data->mass, 'mass assignment failed (1)');
        $this->assertEquals(2242, $data->assignment, 'mass assignment failed (2)');
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

        $this->assertEquals('testing', $data->mass, 'constructor assignment failed (1)');
        $this->assertEquals(2242, $data->assignment, 'constructor assignment failed (2)');
    }

    /**
     * @test
     */
    function it_validates_attributes()
    {
        $data = new Helpers\TestDataObject();

        // validate empty data against single required rule
        $this->assertFalse($data->validate(), 'empty should not pass validation');

        $messages = $data->messages();
        $this->assertInstanceOf(MessageBag::class, $messages, 'validation messages not of correct type');
        $this->assertCount(1, $messages, 'validation messages should have 1 message');
        $this->assertRegExp(
            '#name .*is required#i',
            $messages->first(),
            'validation message not as expected for empty data'
        );

        // validate partially incorrect data
        $data->name = 'Valid name';
        $data->list = 'not an array';

        $this->assertFalse($data->validate(), 'incorrect data should not pass validation');

        $messages = $data->messages();
        $this->assertCount(1, $messages, 'validation messages should have 1 message');
        $this->assertRegexp(
            '#list .*must be an array#i',
            $messages->first(),
            'validation message not as expected for incorrect data'
        );

        // validate correct data should be okay
        $data->name = 'Valid name';
        $data->list = [ 'one' => 'present' ];

        $this->assertTrue($data->validate(), 'Correct data should pass validation');
    }


    // ------------------------------------------------------------------------------
    //      Restrictive measures
    // ------------------------------------------------------------------------------

    /**
     * @test
     * @expectedException \Czim\DataObject\Exceptions\UnassignableAttributeException
     * @expectedExceptionMessageRegExp #not allowed .*does_not_exist#i
     */
    function it_throws_an_exception_when_assigning_to_disallowed_keys()
    {
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
     * @expectedException \Czim\DataObject\Exceptions\UnassignableAttributeException
     * @expectedExceptionMessageRegExp #not allowed .*does_not_exist#i
     */
    function it_throws_an_exception_when_assigning_to_disallowed_keys_for_mass_assignment()
    {
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
        $this->assertEquals('okay', $data->name, 'Should still allow normal assignment');
    }

    /**
     * @test
     * @expectedException \Czim\DataObject\Exceptions\UnassignableAttributeException
     * @expectedExceptionMessageRegExp #not allowed .*magic#i
     */
    function it_throws_an_exception_when_assigning_by_magic_if_disallowed_entirely()
    {
        $data = new Helpers\TestMagiclessDataObject();

        $data->magic_blows_up = 'fails';
    }

    /**
     * @test
     * @depends it_throws_an_exception_when_assigning_by_magic_if_disallowed_entirely
     * @expectedException \Czim\DataObject\Exceptions\UnassignableAttributeException
     * @expectedExceptionMessageRegExp #not allowed .*magic#i
     */
    function it_throws_an_exception_when_assigning_by_array_access_if_disallowing_magic()
    {
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

        $this->assertInternalType('array', $array, 'toArray() did not return array');
        $this->assertCount(2, $array, 'incorrect item count');
        $this->assertArraySubset(
            [
                'mass'       => 'testing',
                'assignment' => 2242,
            ],
            $array, 'incorrect array contents'
        );
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

        $this->assertEquals('{"mass":"testing","assignment":2242}', $data->toJson(), 'incorrect toJson result');
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

        $this->assertEquals('{"mass":"testing","assignment":2242}', $json, 'incorrect stringified result');
    }

    /**
     * @test
     */
    function it_is_convertable_to_an_object()
    {
        $data = new Helpers\TestDataObject([
            'mass'       => 'testing',
            'assignment' => 2242,
        ]);

        $object = $data->toObject();

        $this->assertTrue(is_object($object), "not an object");
        $this->assertEquals('testing', $object->mass, 'incorrect property (1)');
        $this->assertEquals(2242, $object->assignment, 'incorrect property (2)');
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

        $this->assertEquals(3, $data->count());
        $this->assertCount(3, $data);
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

        $this->assertInternalType('array', $array, 'nested toArray() did not return array');
        $this->assertCount(2, $array, 'incorrect item count');
        $this->assertArraySubset(
            [
                'contents' => [
                    'mass'       => 'testing',
                    'assignment' => 2242,
                ],
                'more' => [
                    [ 'a' => 'b' ],
                ],
            ],
            $array, 'incorrect nested array contents'
        );
    }
}
