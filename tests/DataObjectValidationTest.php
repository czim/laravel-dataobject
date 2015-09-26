<?php
namespace Czim\DataObject\Test;

use Czim\DataObject\DataObjectServiceProvider;
use Czim\DataObject\Test\Helpers\TestArrayValidationDataObject;
use Czim\DataObject\Test\Helpers\TestBrokenNestedDataObject;
use Czim\DataObject\Test\Helpers\TestDataObject;
use Czim\DataObject\Test\Helpers\TestNestedDataObject;

class DataObjectValidationTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [
            DataObjectServiceProvider::class,
        ];
    }


    // ------------------------------------------------------------------------------
    //      Nested DataObject validation
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_validates_nested_data_objects_for_required_nested_invalid_data()
    {
        $data = new TestNestedDataObject();

        $this->assertFalse($data->validate(), "validation should fail for empty");

        $nestedData = new TestDataObject([ 'irrelevant' => 'nothing' ]);

        $this->assertFalse($nestedData->validate(), "validation should fail for empty nested (on its own)");

        $data->nested = $nestedData;

        $this->assertFalse($data->validate(), "validation should fail for empty nested (when nested)");

        // the messages should indicate that the name field is missing from the nested object
        $messages = $data->messages();

        $this->assertCount(2, $messages, "should be two messages in the bag (1 for nested, 1 for nested content");
        $this->assertRegExp(
            '#name .*required#i',
            $messages->get('nested.name')[0],
            'Incorrect or missing message for nested name'
        );
        $this->assertEquals(
            'validation.dataobject',
            $messages->get('nested')[0],
            'Incorrect or missing message for nested main'
        );

        // Note that the validation.dataobject message is the custom message for which there is
        // no translation in this test!
    }

    /**
     * @test
     */
    function it_validates_nested_data_objects_for_required_nested_valid_data()
    {
        $data         = new TestNestedDataObject();
        $data->nested = new TestDataObject([ 'name' => 'no problem' ]);

        $this->assertTrue($data->validate(), "validation should pass for valid nested data");
    }

    /**
     * @test
     * @depends it_validates_nested_data_objects_for_required_nested_valid_data
     */
    function it_it_validates_nested_data_objects_for_optional_invalid_data()
    {
        $data = new TestNestedDataObject();
        $data->nested = [ 'name' => 'no problem' ];
        $data->more   = new TestDataObject([ 'irrelevant' => 'nothing' ]);

        $this->assertFalse($data->validate(), "validation should fail for invalid optional nested data");
        $this->assertRegExp('#name .*required#i', $data->messages()->get('more.name')[0]);
    }

    /**
     * @test
     * @depends it_validates_nested_data_objects_for_required_nested_valid_data
     */
    function it_it_validates_nested_data_objects_for_optional_valid_data()
    {
        $data = new TestNestedDataObject();
        $data->nested = new TestDataObject([ 'name' => 'no problem' ]);

        // should pass when more is empty
        $this->assertTrue($data->validate(), "validation should pass for omitted optional nested data");

        $data->more = ['name' => 'also fine'];

        // should also pass when more is valid
        $this->assertTrue($data->validate(), "validation should pass for valid optional nested data");
    }

    /**
     * @test
     */
    function it_fails_validation_if_value_for_nested_dataobject_rule_could_not_be_interpreted()
    {
        $data = new TestNestedDataObject();
        $data->nested = 'not a dataobject, or even an array';

        $this->assertFalse($data->validate(), "validation should fail for uninterpretable dataobject value");
        $this->assertRegExp(
            '#not .*interpret.* as testdataobject#i',
            $data->messages()->first(),
            "incorrect validation message for uninterpretable dataobject value"
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp # not .*validatable dataobject#i
     */
    function it_throws_an_exception_if_a_dataobject_validation_rule_does_not_reference_a_data_object()
    {
        $data = new TestBrokenNestedDataObject();

        $data->nested = [ 'nothing' => 'to', 'see' => 'here' ];

        $data->validate();
    }


    // ------------------------------------------------------------------------------
    //      Array validation
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_allows_the_use_of_the_each_validation_rule_for_arrays()
    {
        $data = new TestArrayValidationDataObject();

        $data->array = [ 'string', 'longer string' ];

        $this->assertTrue($data->validate(), "Should validate with valid data");

        $data->array = [ 34, 'longer string', 'tiny' ];

        $this->assertFalse($data->validate(), "Should not validate with invalid data");

        // only the item with index 0 and 2 are invalid
        $messages = $data->messages();

        $this->assertGreaterThanOrEqual(2, $messages->count(), "Should be at least 2 messages");

        $this->assertRegExp(
            '#array .*at least 5 characters#i',
            $messages->get('array.2')[0],
            "Validation message (array.2) incorrect"
        );
    }


    // ------------------------------------------------------------------------------
    //      Arrayable / JsonAble with nesting
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_is_recursively_arrayable_for_nested_attributes()
    {
        $data = new TestNestedDataObject([
            'nested' => new TestDataObject([
                'name' => 'beautiful name',
            ]),
            'random' => 'extra',
        ]);

        $array = $data->toArray();

        $this->assertInternalType('array', $array, 'toArray() did not return array');
        $this->assertCount(2, $array, 'incorrect item count');
        $this->assertArraySubset(
            [ 'random' => 'extra' ],
            $array, 'incorrect nested array contents'
        );

        $this->assertInternalType('array', $array['nested'], 'toArray() nested object was not an array');
        $this->assertArraySubset(
            [ 'name' => 'beautiful name', ],
            $array['nested'], 'incorrect nested array contents'
        );
    }

    /**
     * @test
     */
    function it_is_recursively_jsonable_for_nested_attributes()
    {
        $data = new TestNestedDataObject([
            'nested' => new TestDataObject([
                'name' => 'beautiful name',
            ]),
            'random' => 'extra',
        ]);

        $this->assertEquals(
            '{"nested":{"name":"beautiful name"},"random":"extra"}',
            $data->toJson(),
            'incorrect nested toJson result'
        );
    }

}
