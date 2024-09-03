# Type

## Introduction

The type component provides a set functions to ensure that a given value is of a specific type **at Runtime**.
It aims to provide a solution for the [Parse, Don't Validate](https://lexi-lambda.github.io/blog/2019/11/05/parse-don-t-validate/) problem.

## Usage

```php
use Psl;
use Psl\Type;

$untrustedInput = $request->get('input');

// Turns a string-like value into a non-empty-string
$trustedInput = Type\non_empty_string()->coerce($untrustedInput);

// Or assert that its already a non-empty-string
$trustedInput = Type\non_empty_string()->assert($untrustedInput);

// Or check if its a non-empty-string
$isTrustworthy = Type\non_empty_string()->matches($untrustedInput);
```

Every type provided by this component is an instance of `Type\TypeInterface<Tv>`.
This interface provides the following methods:

- `matches(mixed $value): $value is Tv` - Checks if the provided value is of the type.
- `assert(mixed $value): Tv` - Asserts that the provided value is of the type or throws an `AssertException` on failure.
- `coerce(mixed $value): Tv` - Coerces the provided value into the type or throws a `CoercionException` on failure.


## Static Analysis

Your static analyzer fully understands the types provided by this component.
But it takes an additional step to activate this knowledge:

### Psalm Integration

Please refer to the [`php-standard-library/psalm-plugin`](https://github.com/php-standard-library/psalm-plugin) repository.

### PHPStan Integration

Please refer to the [`php-standard-library/phpstan-extension`](https://github.com/php-standard-library/phpstan-extension) repository.

## API

### Functions

#### [array_key](array_key.php)

```hack
@pure
Type\array_key(): TypeInterface<string|int>
```

Provides a type that can parse array-keys.

Can coerce from:

* `string`: The [string()](#string) type is used to coerce the input value to a string.
* `int`: The [`int()`](#int) type is used to coerce the input value to an integer.

---

#### [backed_enum](backed_enum.php)

```hack
@pure
@template T of BackedEnum
Type\backed_enum(class-string<T> $enum): TypeInterface<T>
```

Provides a type that can parse backed-enums.

Can coerce from:

* `string` when `T` is a string-backed enum.
* `int` when `T` is an integer-backed enum.

---

#### [backed_enum_value](backed_enum_value.php)

```hack
@pure
@template T of BackedEnum
Type\backed_enum_value(class-string<T> $enum): TypeInterface<value-of<T>>
```

Provides a type that can verify a value matches a backed enum value.

Can coerce from:

* `string|int` when `T` is a string-backed enum.
* `int|numeric-string` when `T` is an integer-backed enum.

---

#### [bool](bool.php)

```hack
@pure
Type\bool(): TypeInterface<bool>
```

Can coerce from:

* `bool`: `true` or `false`
* `int`: `1` or `0`
* `string`: `'1'` or `'0'`

---

#### [class_string](class_string.php)

```hack
@pure
@template T of object
Type\class_string(class-string<T> $classname): TypeInterface<class-string<T>>
```

Provides a type that can parse class-strings.

Can coerce from:

* `string` when `T` is a string that indicates the Fully-Qualified class-name of `T` or one of its subclasses.

Examples:

```php
use Psl\Type;

Type\class_string(stdClass::class)->assert(stdClass::class);
Type\class_string(SomeInterface::class)->assert(SomeInterface::class);
Type\class_string(SomeInterface::class)->assert(SomeImplementation::class);
```

---

#### [converted](class_string.php)

```hack
@pure
@template I of mixed
@template O of mixed
Type\converted(
    TypeInterface<I> $from,
    TypeInteface<O> $into,
    (Closure(I): O) $converter
): TypeInterface<O>
```

Provides a type `O` that can be converted from a type `I` using a converter function.

* During `assert()`, this type will assert that the value matches the `$into of O` type. No conversion will be applied.
* During `coerce()` this type will convert the input value through a couple of different stages:
  * When the original input is already of the output type, it is returned as is.
  * `coerce_input(mixed): I` The mixed input value will be coerced to the `$from of I` type.
  * `covert(I): mixed` The input of type `I` will be converted using the `$converter` function.
  * `coerce_output(mixed): O` The output of the converter function will be coerced to the `$into of O` type.
  
These are some examples on how the type can be used:

```php
use Psl\Type;

$dateTimeType = Type\converted(
    Type\string(),
    Type\instance_of(DateTimeImmutable::class),
    static function (string $value): DateTimeImmutable {
        $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);
        if (!$date) {
            // Exceptions will be transformed to CoerceException
            throw new \RuntimeException('Invalid format given. Expected date to be of format {format}');
        }
    
        return $date;
    }
);

$emailType = Type\converted(
    Type\string(),
    Type\instance_of(EmailValueObject::class),
    static function (string $value): EmailValueObject {
        // Exceptions will be transformed to CoerceException
        return EmailValueObject::tryParse($value);
    }
);

$shape = Type\shape([
    'email' => $emailType,
    'dateTime' => $dateTimeType
]);

// Coerce will convert from -> into, If the provided value is already into - it will skip conversion.
$coerced = $shape->coerce($data);

// Assert will check if the value is of the type it converts into! 
$shape->assert($coerced); 
```

This type can also be used to transform array-shaped values into custom Data-Transfer-Objects:

```php
use Psl\Type;
use Psl\Type\TypeInterface;

/**
* @psalm-immutable
*/
final class Person {  
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
    ) {
    }
  
    /**
     * @pure
     *
     * @return TypeInterface<self>
     */
    public static function type(): TypeInterface {
        return Type\converted(
            Type\shape([
                'firstName' => Type\string(),
                'lastName' => Type\string(),
            ]),
            Type\instance_of(Person::class),
            fn (array $data): Person => new Person(
                $data['firstName'],
                $data['lastName']
            )
        );
    }
  
    /**
     * @pure
     */
    public static function parse(mixed $data): self
    {
        return self::type()->coerce($data);
    }
}

// The Person::type() function can now be used as its own type so that it can easily be reused throughout your application.

$nested = Type\shape([
    'person' => Person::type(),
]);
```

When the converter coercion fails, you will get detailed information about what went wrong in which specific conversion stage:

> Could not coerce "int" to type "class-string<stdClass>" **at path "coerce_input(int): class-string<stdClass>**"

>  Could not coerce "int" to type "string" **at path "convert(string): string": Internal converter error**.

>  Could not coerce "string" to type "class-string<stdClass>" **at path "coerce_output(string): class-string<stdClass>**".

---

#### [dict](dict.php)

```hack
@pure
@template Tk of array-key
@template Tv of mixed
Type\dict(
    TypeInterface<Tk> $key_type,
    TypeInterface<Tv> $value_type
): TypeInterface<array<Tk, Tv>>
```

Provides a type that can parse array dictionaries. The `Tv` type can consist out of advanced types like shapes, vectors, or other dictionaries.

Can coerce from:

* `iterable`: The iterable will be iterated:
  * Every item's key will be coerced by the provided `Tk` type.
  * Every item's value will be coerced by the provided `Tv` type.

These are some examples on how the type can be used:

```php
use Psl\Type;

$dict = Type\dict(
    Type\string(),
    Type\shape([
        'title' => Type\string(),
        'content' => Type\string(),
    ])
);

$dict->assert([
    'string-key' => [
        'title' => 'Hello world',
        'content' => 'A book that greets the world in all possible languages!',
    ]
]);
```

When the iterable does not match the specified dictionary, you will get detailed information about what went wrong exactly:

> Expected "dict<string, array{'title': string, 'content': string}>", got "int" **at path "key(123)"**.

> Expected "dict<string, array{'title': string, 'content': string}>", got "int" **at path "foo.title"**.

---

#### [f32](f32.php)

```hack
@pure
Type\f32(): TypeInterface<f32>
```

Provides a type that can parse floats with a range `float<-3.40282347E+38, 3.40282347E+38>`.

Can coerce from:

* This type will use the same coercion rules as the [`float()`](#float) type whilst guarding the float range.

---

#### [f64](f64.php)

```hack
@pure
Type\f64(): TypeInterface<f64>
```

Provides a type that can parse floats with a range `float<-1.7976931348623157E+308, 1.7976931348623157E+308>`.

Can coerce from:

* This type will use the same coercion rules as the [`float()`](#float) type whilst guarding the float range.

---

#### [float](float.php)

```hack
@pure
Type\float(): TypeInterface<float>
```

Provides a type that can parse floats.

Can coerce from:

* `float`: Will convert the float AS-IS.
* `int`: Will convert the integer into a float.
* `string`: Will convert the string to a float if it is a numeric string or if it matches a float pattern.
* `\Stringable`: Will call `__toString()` on the object and apply the same logic as on regular strings.

---

#### [i8](i8.php)

```hack
@pure
Type\i8(): TypeInterface<int<-128, 127>>
```

Provides a type that can parse integers with a range `int<-128, 127>`.

Can coerce from:

* This type will use the same coercion rules as the [`int()`](#int) type whilst guarding the integer range.

---

#### [i16](i16.php)

```hack
@pure
Type\i16(): TypeInterface<i16>
```

Provides a type that can parse integers with a range `int<-32768, 32767>`.

Can coerce from:

* This type will use the same coercion rules as the [`int()`](#int) type whilst guarding the integer range.

---

#### [i32](i32.php)

```hack
@pure
Type\i32(): TypeInterface<i32>
```

Provides a type that can parse integers with a range `int<-2147483648, 2147483647>`.

Can coerce from:

* This type will use the same coercion rules as the [`int()`](#int) type whilst guarding the integer range.

---

#### [i64](i64.php)

```hack
@pure
Type\i64(): TypeInterface<i64>
```

Provides a type that can parse integers with a range `int<min, max>`.

Can coerce from:

* This type will use the same coercion rules as the [`int()`](#int) type whilst guarding the integer range.

---

#### [instance_of](instance_of.php)

```hack
@pure
@template T of object
Type\instance_of(class-string<T> $classname): TypeInterface<T>
```

Provides a type that can parse objects of a specific class.

Can coerce from:

* `object` : Only objects and class instances are accepted and are validated against the provided (base) class-name.

Examples:

```php
use Psl\Type;

Type\instance_of(stdClass::class)->assert(stdClass::class);
Type\instance_of(SomeInterface::class)->assert($someImplementation);
```

---

#### [int](int.php)

```hack
@pure
Type\int(): TypeInterface<int>
```

Provides a type that can parse integers.

Can coerce from:

* `int`: Will convert the integer AS-IS.
* `float`: Will return the whole part of the float if the decimal part is `.00`
* `string`: Will convert the string to an integer if it is a valid integer string.
* `\Stringable`: Will call `__toString()` on the object and apply the same logic as on regular strings.

---

#### [intersection](intersection.php)

```hack
@pure
@template TFirst of mixed
@template TSecond of mixed
@template ...TRest of mixed
Type\intersection(
    TypeInterface<TFirst> $first,
    TypeInterface<TSecond> $second,
    TypeInterface<TRest> ...$rest
): TypeInterface<TFirst&TSecond&TRest>
```

Provides a type that can parse an intersection of types.

Can coerce from:

* `TFirst`: The provided value will be coerced by the first type and be asserted against the second type.
* `TSecond`: When the `TFirst` coercion fails, the provided value will be coerced by the second type and be asserted against the first type.

When multiple `...TRest` arguments are provided, the coercion will be applied in the order of the provided types.
Every additional type will result in an intersection of the previous types.
For example:

```
intersection(A, B, C) === intersection(intersection(A, B), C)
```

---

#### [is_nan](is_nan.php)

!> This function is a predicate and does not return a type.

```hack
@pure
Type\is_nan(mixed $value): bool
```

Finds whether a float is NaN ( not a number ).

---

#### [iterable](iterable.php)

```hack
@pure
@template Tk of array-key
@template Tv of mixed
Type\iterable(
    TypeInterface<Tk> $key_type,
    TypeInterface<Tv> $value_type
): TypeInterface<iterable<Tk, Tv>>
```

Provides a type that can parse iterables. The `Tv` type can consist out of advanced types like shapes, vectors, or other dictionaries.

Can coerce from:

* `iterable`: The iterable will be iterated:
    * Every item's key will be coerced by the provided `Tk` type.
    * Every item's value will be coerced by the provided `Tv` type.

These are some examples on how the type can be used:

```php
use Psl\Type;

$iterable = Type\iterable(
    Type\string(),
    Type\shape([
        'title' => Type\string(),
        'content' => Type\string(),
    ])
);

$iterable->assert([
    'string-key' => [
        'title' => 'Hello world',
        'content' => 'A book that greets the world in all possible languages!',
    ]
]);
```

When the iterable does not match the specified type, you will get detailed information about what went wrong exactly:

> Expected "iterable<string, array{'title': string, 'content': string}>", got "int" **at path "key(123)"**.

> Expected "iterable<string, array{'title': string, 'content': string}>", got "int" **at path "foo.title"**.

---

#### [literal_scalar](literal_scalar.php)

```hack
@pure
@template T of string|int|float|bool
Type\literal_scalar(T): TypeInterface<T>
```

Provides a type that can parse a literal scalar value.

Can coerce from:

* `T`: The provided value will be compared against the expected value. If it matches, the coercion has succeeded.
* `string`: The provided value will be coerced into a string and compared against the expected value.
* `int`: The provided value will be coerced into an integer and compared against the expected value.
* `float`: The provided value will be coerced into a float and compared against the expected value.
* `bool`: The provided value will be coerced into a boolean and compared against the expected value.

Examples:

```php
use Psl\Type;

Type\literal_scalar('hello')->assert('hello');
```

#### [map](map.php)

```hack
@pure
@template Tk of array-key
@template Tv of mixed
Type\map(
    TypeInterface<Tk> $key_type,
    TypeInterface<Tv> $value_type
): TypeInterface<MapInterface<Tk, Tv>>
```

Provides a type that can parse into a `Psl\Collection\MapInterface`. The `Tv` type can consist out of advanced types like shapes, vectors, or other dictionaries.

Can coerce from:

* `iterable`: The iterable will be iterated:
    * Every item's key will be coerced by the provided `Tk` type.
    * Every item's value will be coerced by the provided `Tv` type.

These are some examples on how the type can be used:

```php
use Psl\Type;

$map = Type\map(
    Type\string(),
    Type\shape([
        'title' => Type\string(),
        'content' => Type\string(),
    ])
);

$result = $map->coerce([
    'string-key' => [
        'title' => 'Hello world',
        'content' => 'A book that greets the world in all possible languages!',
    ]
]);
```

When the iterable does not match the specified type, you will get detailed information about what went wrong exactly:

> Expected "Psl\Collection\MapInterface<string, array{'title': string, 'content': string}>", got "int" **at path "key(123)"**.

> Expected "Psl\Collection\MapInterface<string, array{'title': string, 'content': string}>", got "int" **at path "foo.title"**.

---

#### [mixed](mixed.php)
```hack
@pure
Type\mixed(): TypeInterface<mixed>
```

Provides a type that can parse `mixed`. (so basically anything)

Can coerce from:

* anything : mixed is the most permissive type, so it will accept any input value.

---

#### [mixed_dict](mixed_dict.php)

```hack
@pure
Type\mixed_dict(): TypeInterface<array<array-key, mixed>>
```

Provides a type that can parse any array dictionaries.

Can coerce from:

* `iterable`: It will make sure the array-key is a string or an integer allowing it to have any value.

---

#### [mixed_vec](mixed_vec.php)

```hack
@pure
Type\mixed_vec(): TypeInterface<list<mixed>>
```

Provides a type that can parse any array list.

Can coerce from:

* `iterable`: It will make sure the provided value is an iterable and will coerce every item to mixed.

---

#### [mutable_map](mutable_map.php)

```hack
@pure
@template Tk of array-key
@template Tv of mixed
Type\mutable_map(
    TypeInterface<Tk> $key_type,
    TypeInterface<Tv> $value_type
): TypeInterface<MutableMapInterface<Tk, Tv>>
```

Provides a type that can parse into a `Psl\Collection\MutableMapInterface`. The `Tv` type can consist out of advanced types like shapes, vectors, or other dictionaries.

Can coerce from:

* `iterable`: The iterable will be iterated:
    * Every item's key will be coerced by the provided `Tk` type.
    * Every item's value will be coerced by the provided `Tv` type.

These are some examples on how the type can be used:

```php
use Psl\Type;

$map = Type\mutable_map(
    Type\string(),
    Type\shape([
        'title' => Type\string(),
        'content' => Type\string(),
    ])
);

$result = $map->coerce([
    'string-key' => [
        'title' => 'Hello world',
        'content' => 'A book that greets the world in all possible languages!',
    ]
]);
```

When the iterable does not match the specified type, you will get detailed information about what went wrong exactly:

> Expected "Psl\Collection\MutableMapInterface<string, array{'title': string, 'content': string}>", got "int" **at path "key(123)"**.

> Expected "Psl\Collection\MutableMapInterface<string, array{'title': string, 'content': string}>", got "int" **at path "foo.title"**.

---

#### [mutable_set](mutable_set.php)

```hack
@pure
@template T of array-key
Type\mutable_set(
    TypeInterface<T> $type,
): TypeInterface<MutableSetInterface<Tk, Tv>>
```

Provides a type that can parse into a `Psl\Collection\MutableSetInterface`.

Can coerce from:

* `iterable`: The iterable will be iterated:
    * Every item's value will be coerced by the provided `T` type.

These are some examples on how the type can be used:

```php
use Psl\Type;

$set = Type\mutable_set(
    Type\string(),
);

$result = $set->coerce(['a', 'b', 'c', 'd', 'd']);
```

When the iterable does not match the specified type, you will get detailed information about what went wrong exactly:

> Expected "Psl\Collection\MutableSetInterface<string>", got "int" **at path "foo"**.

---

#### [mixed](mixed.php)
```hack
@pure
Type\mixed(): TypeInterface<mixed>
```

Provides a type that can parse `mixed`. (so basically anything)

Can coerce from:

* anything : mixed is the most permissive type, so it will accept any input value.

---

#### [mutable_vector](mutable_vector.php)

```hack
@pure
@template Tv of mixed
Type\mutable_vector(TypeInterface<Tv> $value_type): TypeInterface<MutableVectorInterface<Tv>>
```

Provides a type that can parse `Psl\Collection\MutableVectorInterface`. The `Tv` type can consist out of advanced types like shapes, vectors, or other dictionaries.

Can coerce from:

* `iterable`: The iterable will be iterated:
    * Every item's value will be coerced by the provided `Tv` type.

These are some examples on how the type can be used:

```php
use Psl\Type;

$vector = Type\vector(Type\shape([
    'user' => Type\string(),
    'comment' => Type\string()
]));

$result = $vector->coerce([
    ['user' => 'john', 'comment' => 'hello'],
    ['user' => 'jane', 'comment' => 'world']
]);
```

When the iterable value does not match the specified type, you will get detailed information about what went wrong exactly:

> Expected "Psl\Collection\MutableVectorInterface<array{'user': string, 'comment': string}>", got "int" **at path "0.user".**

> Could not coerce "stdClass" to type "Psl\Collection\MutableVectorInterface<array{'user': string, 'comment': string}>" **at path "foo.user".**

---

#### [non_empty_dict](non_empty_dict.php)

```hack
@pure
@template Tk of array-key
@template Tv of mixed
Type\non_empty_dict(TypeInterface<Tk> $key_type, TypeInterface<Tv> $value_type): TypeInterface<non-empty-array<Tk, Tv>>
```

Provides a type that can parse non-empty array dictionaries.

Can coerce from:

* `iterable`: It uses the same coercion rules as the [`dict()`](#dict) type whilst guarding the non-empty array.

---

#### [non_empty_string](non_empty_string.php)

```hack
@pure
Type\non_empty_string(): TypeInterface<non-empty-string>
```

Provides a type that can parse a non-empty-string.

Can coerce from:

* This type will use the same coercion rules as the [`string()`](#string) type whilst guarding the non-empty string.

---

#### [non_empty_vec](non_empty_vec.php)

```hack
@pure
@template Tv of mixed
Type\non_empty_vec(TypeInterface<Tv> $value_type): TypeInterface<non-empty-list<Tv>>
```

Provides a type that can parse non-empty array lists.

Can coerce from:

* `iterable`: It uses the same coercion rules as the [`vec()`](#vec) type whilst guarding the non-empty array.

---

#### [nonnull](nonnull.php)
```hack
@pure
Type\nonnull(): TypeInterface<non-null>
```

Provides a type that can parse `non-null`. Non-null is considered to be equal to `mixed` without `null.

Can coerce from:

* `non-null` : This type accepts any input you give it, except `null`.

Both `assert()` and `coerce()` are designed to narrow down the provided type:

```php
use Psl\Type;

$nonnull = Type\nonnull();
$nonnull->assert($stringOrNull);

// Your static analyzer will know that $stringOrNull is a string !

```

---

#### [null](null.php)
```hack
@pure
Type\null(): TypeInterface<null>
```

Provides a type that can parse `null`. (so basically anything)

Can coerce from:

* `null` : Only `null` is accepted as a valid input value.

---

#### [nullable](nullable.php)
```hack
@pure
@template Tv of mixed
Type\nullable(TypeInterface $inner_type): TypeInterface<null|Tv>
```

Provides a type that can parse `null | Tv`.

Can coerce from:

* `null` : The provided `null` value will be returned as is.
* `mixed` : The provided value will be coerced by the provided `Tv` type.

```php
use Psl\Type;

$nullableString = Type\nullable(Type\string());
$nullable->assert($stringOrNull);
```

---

#### [num](num.php)

```hack
@pure
Type\num(): TypeInterface<int|float>
```

Provides a type that can parse numeric values.

Can coerce from:

* `int`: The [`int()`](#int) type is used to coerce the input value to an integer.
* `float`: The [`float()`](#float) type is used to coerce the input value to a float.

---

#### [numeric_string](numeric_string.php)

```hack
@pure
Type\numeric_string(): TypeInterface<numeric-string>
```

Provides a type that can parse a numeric-string.

Can coerce from:

* `string`: The provided string will be checked if it is a numeric string.
* `num`: The provided numeric value will be converted to a string.
* `\Stringable`: The provided value will be coerced to a string and checked if it is a numeric string.

---

#### [object](object.php)
```hack
@pure
Type\object(): TypeInterface<object>
```

Provides a type that can parse `mixed`. (so basically anything)

Can coerce from:

* `object` : Only objects and class instances are accepted as a valid input value.

---

#### [optional](optional.php)
```hack
@pure
@template Tv of mixed
Type\optional(TypeInterface $inner_type): TypeInterface<?Tv>
```

Provides a type that can parse `?Tv`.

Can coerce from:

* `undefined` : The type will be ignored when being validated inside a shape.
* `mixed` : The provided value will be coerced by the provided `Tv` type.

```php
use Psl\Type;

$nullableString = Type\optional(Type\string());
$nullable->assert($stringOrNull);
```

---

#### [positive_int](positive_int.php)

```hack
@pure
Type\positive_int(): TypeInterface<positive-int>
```

Provides a type that can parse integers with a range `int<1, max>`.

Can coerce from:

* This type will use the same coercion rules as the [`int()`](#int) type whilst guarding the integer range.

---

#### [resource](resource.php)

```hack
@pure
@template Tkind of string<get_resource_type()>
Type\resource(?Tkind $kind): TypeInterface<resource<Tkind>>
```

Provides a type that can parse `resource`.
When a kind is specified, the resource will be checked if it is of that kind by using the [get_resource_type](https://www.php.net/manual/en/function.get-resource-type.php) method.

Can coerce from:

* `resource` : Only resources are accepted as a valid input value.
  
Examples:

```php
use Psl\Type;

$resource = Type\resource()->assert($resource);
$curlResource = Type\resource('curl')->assert($resource);
$streamResource = Type\resource('stream')->assert($resource);
```

---

#### [scalar](scalar.php)

```hack
@pure
Type\scalar(): TypeInterface<string|bool|int|float>
```

Provides a type that can parse a scalar value.

Can coerce from:

* `string`: First, the type will attempt to coerce the provided value into a string.
* `bool`: If that fails, the type will attempt to coerce the provided value into a string.
* `int`: Next, the type will attempt to coerce the provided value into an integer.
* `float`: Finally, the type will attempt to coerce the provided value into a float.

---

#### [set](set.php)

```hack
@pure
@template T of array-key
Type\set(
    TypeInterface<T> $type,
): TypeInterface<SetInterface<Tk, Tv>>
```

Provides a type that can parse into a `Psl\Collection\SetInterface`.

Can coerce from:

* `iterable`: The iterable will be iterated:
    * Every item's value will be coerced by the provided `T` type.

These are some examples on how the type can be used:

```php
use Psl\Type;

$set = Type\set(
    Type\string(),
);

$result = $set->coerce(['a', 'b', 'c', 'd', 'd']);
```

When the iterable does not match the specified type, you will get detailed information about what went wrong exactly:

> Expected "Psl\Collection\SetInterface<string>", got "int" **at path "foo"**.

---

#### [shape](shape.php)

```hack
@pure
@template Tk of array-key
@template Tv of mixed
Type\shape(dict<Tv, Tk> $elements, bool $allow_unknown_fields = false): TypeInterface<array<Tk, Tv>>
```

Provides a type that can parse (deeply nested) iterables. A shape can consist out of multiple child-shapes and structures.

* During `assert()` the type will assert that every child has a valid child-type.
* During `coerce()` the type will coerce every child element into its corresponding child-type.

```php
use Psl\Type;

$shape = Type\shape([
    'name' => Type\string(),
    'articles' => Type\vec(Type\shape([
        'title' => Type\string(),
        'content' => Type\string(),
        'likes' => Type\int(),
        'comments' => Type\optional(
            Type\vec(Type\shape([
                'user' => Type\string(),
                'comment' => Type\string()
            ]))
        ),
    ])),
    'dictionary' => Type\dict(Type\string(), Type\vec(Type\shape([
        'title' => Type\string(),
        'content' => Type\string(),
    ]))),
    'pagination' => Type\optional(Type\shape([
        'currentPage' => Type\uint(),
        'totalPages' => Type\uint(),
        'perPage' => Type\uint(),
        'totalRows' => Type\uint(),
    ]))
]);

$validData = $shape->coerce([
    'name' => 'ok',
    'articles' => [
        [
            'title' => 'ok',
            'content' => 'ok',
            'likes' => 1,
            'comments' => [
                [
                    'user' => 'ok',
                    'comment' => 'ok'
                ],
                [
                    'user' => 'ok',
                    'comment' => 'ok',
                ]
            ]
        ]
    ],
    'dictionary' => [
        'key' => [
            [
                'title' => 'ok',
                'content' => 'ok',
            ]
        ]
    ]
]);
```

When the data structure does not match the specified shape, you will get detailed information about what went wrong exactly:

> Expected "array{'name': string, 'articles': vec<array{'title': string, 'content': string, 'likes': int, 'comments'?: vec<array{'user': string, 'comment': string}>}>, 'dictionary': dict<string, vec<array{'title': string, 'content': string}>>, 'pagination'?: array{'currentPage': uint, 'totalPages': uint, 'perPage': uint, 'totalRows': uint}}", got "int" **at path "articles.0.comments.0.user"**.

---

#### [string](string.php)

```hack
@pure
Type\string(): TypeInterface<string>
```

Provides a type that can parse string.

Can coerce from:

* `string`: Will return the string AS-IS.
* `int`: Will convert the integer to a string.
* `\Stringable`: Will call `__toString()` on the object.

If you wish to convert a `float` to a string, please use [numeric_string()](#numeric_string) instead.

---

#### [u8](u8.php)

```hack
@pure
Type\u8(): TypeInterface<u8>
```

Provides a type that can parse integers with a range `int<0, 255>`.

Can coerce from:

* This type will use the same coercion rules as the [`int()`](#int) type whilst guarding the integer range.

---

#### [u16](u16.php)

```hack
@pure
Type\u16(): TypeInterface<u16>
```

Provides a type that can parse integers with a range `int<0, 65535>`.

Can coerce from:

* This type will use the same coercion rules as the [`int()`](#int) type whilst guarding the integer range.

---

#### [u32](u32.php)

```hack
@pure
Type\u32(): TypeInterface<u32>
```

Provides a type that can parse integers with a range `int<0, 4294967295>`.

Can coerce from:

* This type will use the same coercion rules as the [`int()`](#int) type whilst guarding the integer range.

---

#### [uint](uint.php)

```hack
@pure
Type\uint(): TypeInterface<uint>
```

Provides a type that can parse integers with a range `int<0, max>`.

Can coerce from:

* This type will use the same coercion rules as the [`int()`](#int) type whilst guarding the integer range.

---

#### [union](union.php)

```hack
@pure
@template TFirst of mixed
@template TSecond of mixed
@template ...TRest of mixed
Type\union(
    TypeInterface<TFirst> $first,
    TypeInterface<TSecond> $second,
    TypeInterface<TRest> ...$rest
): TypeInterface<TFirst|TSecond|TRest>
```

Provides a type that can parse a union of types.

Can coerce from:

* `TFirst`: The provided value will be coerced by the first type.
* `TSecond`: When the `TFirst` coercion fails, the provided value will be coerced by the second type.

When multiple `...TRest` arguments are provided, the coercion will be applied in the order of the provided types.
Every additional type will result in a union of the previous types.
For example:

```
union(A, B, C) === union(union(A, B), C)
```

#### [unit_enum](unit_enum.php)

```hack
@pure
@template T of UnitEnum
Type\unit_enum(class-string<T> $enum): TypeInterface<T>
```

Provides a type that can parse unit-enums.

Can coerce from:

* `T`: the type can only coerce from an instance of the provided unit-enum class.

---

#### [vec](vec.php)

```hack
@pure
@template Tv of mixed
Type\vec(TypeInterface<Tv> $value_type): TypeInterface<list<Tv>>
```

Provides a type that can parse array lists. The `Tv` type can consist out of advanced types like shapes, vectors, or other dictionaries.

Can coerce from:

* `iterable`: The iterable will be iterated:
    * Every item's value will be coerced by the provided `Tv` type.

These are some examples on how the type can be used:

```php
use Psl\Type;

$vec = Type\vec(Type\shape([
    'user' => Type\string(),
    'comment' => Type\string()
]));

$vec->assert([
    ['user' => 'john', 'comment' => 'hello'],
    ['user' => 'jane', 'comment' => 'world']
]);
```

When the iterable value does not match the specified type, you will get detailed information about what went wrong exactly:

> Expected "vec<array{'user': string, 'comment': string}>", got "int" **at path "0.user".**

> Could not coerce "stdClass" to type "vec<array{'user': string, 'comment': string}>" **at path "foo.user".**

---

#### [vector](vector.php)

```hack
@pure
@template Tv of mixed
Type\vector(TypeInterface<Tv> $value_type): TypeInterface<VectorInterface<Tv>>
```

Provides a type that can parse `Psl\Collection\VectorInterface`. The `Tv` type can consist out of advanced types like shapes, vectors, or other dictionaries.

Can coerce from:

* `iterable`: The iterable will be iterated:
    * Every item's value will be coerced by the provided `Tv` type.

These are some examples on how the type can be used:

```php
use Psl\Type;

$vector = Type\vector(Type\shape([
    'user' => Type\string(),
    'comment' => Type\string()
]));

$result = $vector->coerce([
    ['user' => 'john', 'comment' => 'hello'],
    ['user' => 'jane', 'comment' => 'world']
]);
```

When the iterable value does not match the specified type, you will get detailed information about what went wrong exactly:

> Expected "Psl\Collection\VectorInterface<array{'user': string, 'comment': string}>", got "int" **at path "0.user".**

> Could not coerce "stdClass" to type "Psl\Collection\VectorInterface<array{'user': string, 'comment': string}>" **at path "foo.user".**

---
