<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Psl\Math;
use Psl\Type;

final class MixedTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\mixed();
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [123, 123];
        yield [0, 0];
        yield ['0', '0'];
        yield ['123', '123'];
        yield [$_ = static::stringable('123'), $_];
        yield [$_ = static::stringable((string) Math\INT16_MAX), $_];
        yield [$_ = static::stringable((string) Math\INT64_MAX), $_];
        yield [(string) Math\INT64_MAX, (string) Math\INT64_MAX];
        yield [Math\INT64_MAX, Math\INT64_MAX];
        yield [$_ = static::stringable('-321'), $_];
        yield ['-321', '-321'];
        yield [-321, -321];
        yield ['7', '7'];
        yield ['07', '07'];
        yield ['007', '007'];
        yield ['000', '000'];
        yield [$_ = static::stringable('123'), $_];
        yield ['1e2', '1e2'];
        yield [$_ = static::stringable('1e2'), $_];
        yield ['1.23e45', '1.23e45'];
        yield ['.23', '.23'];
        yield [$_ = static::stringable('1.23'), $_];
        yield [(float) Math\INT64_MAX, (float) Math\INT64_MAX];
        yield ['9223372036854775808', '9223372036854775808'];
        yield ['-.9e2', '-.9e2'];
        yield ['-0.7e2', '-0.7e2'];
        yield [false, false];
        yield [0, 0];
        yield [1, 1];
        yield [true, true];
        yield [[], []];
        yield [
            $_ = new class {},
            $_,
        ];
        yield [null, null];
        yield [STDIN, STDIN];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [null];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [static::getType(), 'mixed'];
    }

    #[DataProvider('getInvalidValues')]
    #[Override]
    public function testInvalidAssertion(mixed $value): void
    {
        $this->addToAssertionCount(1);
    }

    #[DataProvider('getInvalidCoercions')]
    #[Override]
    public function testInvalidCoercion(mixed $value): void
    {
        $this->addToAssertionCount(1);
    }

    /**
     * @param mixed $value
     */
    #[DataProvider('getInvalidValues')]
    #[Override]
    public function testInvalidMatches(mixed $value): void
    {
        $this->addToAssertionCount(1);
    }

    public function testItIsAMemoizedType(): void
    {
        static::assertSame(Type\mixed(), Type\mixed());
    }
}
