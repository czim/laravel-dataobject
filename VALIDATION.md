### Validate Nested DataObjects


## Version 2.0 and upward

Note: The old `Czim\DataObject\Validation\Traits\ValidateAsDataObjectTrait` has been removed in version 2.0.0.

A custom validator class has been added: `Czim\DataObject\Validation\CustomValidation`.

This uses simple extended validation rules and may be reused if you want.


## Version 1.4 and below

`Czim\DataObject\Validation\Traits\ValidateAsDataObjectTrait`

Allows the use of the `dataobject` validation rule to have nested data object validation.

Example:
```php
// ... in \YourNameSpace\FirstDataObject
protected $rules = [
    'some_key' => 'dataobject:\YourNameSpace\SecondDataObject', 
];

// ... in \YourNameSpace\SecondDataObject
protected $rules = [
    'present' => 'required|string',
];


// Now if you validate as follows, it will fail

$data = new \YourNameSpace\FirstDataObject([
    'some_key' => [
        'present' => 123,
    ],
]);

// The messages() would then have two messages
//
// 1. for 'some_key.present': 'The present must be a string.'
// 2. for 'some_key':         'validation.dataobject'
//    (for which you can configure translations as normal)

// Note that it would also fail if the value for 'some_key'
// is a not an array or Arrayable:

// returns false
(new \YourNameSpace\FirstDataObject([
    'some_key' => 'some string',
]))->validate();

// returns true
(new \YourNameSpace\FirstDataObject([
    'some_key' => [ 'present' => 'proper string' ],
]))->validate();


// Though it would allow the value to already be a data object

// returns true
(new \YourNameSpace\FirstDataObject([
    'some_key' => (new \YourNameSpace\SecondDataObject([
        'present' => 'proper string',
    ])),
]))->validate();
```

## Version 1.0 and older

### Validate Arrays

Note: this trait was made redundant by Laravel 5.2's new validation rule support for nested arrays. 

`Czim\DataObject\Validation\Traits\ValidateArraysTrait`

Allows the use of the `each` validation rule for more detailed array validation.
 

Example:
```php
// ... in \YourNameSpace\FirstDataObject
protected $rules = [
    'some_list' => 'array|min:1|each:string|each:max,10', 
];

// this would only pass validation if some_list
// - would be an array,
// - would contain at least 1 entry,
// - and each entry in it would be a string of 10 characters at most
```

Mainly added because it is so commonly useful, and this allows for easier installation in some cases.
Plus I do not see how it could hurt.


### Validation Messages

The nested DataObject validation adds a standard translatable validation message for when anything in a nested DataObject validation fails for a given (parent) attribute key.
You may add translations for this as follows:

In `resources/lang/en/validation.php` (or any other relevant language path), add:
```php
    'dataobject' => 'The :attribute contains invalid data.',
```

Replacement values available are `:dataobject`, for the fully qualified namespace of the DataObject validated against,
and `:friendlydataobject` which only shows the class name.
