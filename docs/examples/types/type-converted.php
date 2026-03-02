<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Type;
use Psl\Type\TypeInterface;

$dateTimeType = Type\converted(
    Type\string(),
    Type\instance_of(DateTimeImmutable::class),
    static function (string $value): DateTimeImmutable {
        $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);
        if (!$date) {
            throw new \RuntimeException('Invalid date format');
        }

        return $date;
    },
);

$date = $dateTimeType->coerce('2024-01-15 10:30:00');
// DateTimeImmutable object

$dateTimeType->assert($date);
// Works -- assert checks the output type (DateTimeImmutable), not the input

// Building reusable type-safe parsers for value objects:

final class Person
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
    ) {}

    /** @return TypeInterface<self> */
    public static function type(): TypeInterface
    {
        return Type\converted(
            Type\shape([
                'firstName' => Type\string(),
                'lastName' => Type\string(),
            ]),
            Type\instance_of(Person::class),
            static fn(array $data): Person => new Person($data['firstName'], $data['lastName']),
        );
    }
}

// Now composable with other types:
$shape = Type\shape([
    'person' => Person::type(),
    'role' => Type\string(),
]);
