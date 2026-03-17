<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit\Internal;

use BackedEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Exception\InvariantViolationException;
use Psl\Type\Internal\BackedEnumValueType;
use Psl\Type\Tests\Fixture\IntegerEnum;
use Psl\Type\Tests\Fixture\IntegerEnumWithNoCases;
use Psl\Type\Tests\Fixture\StringEnum;
use Psl\Type\Tests\Fixture\StringEnumWithNoCases;
use ReflectionProperty;

class BackedEnumValueTypeTest extends TestCase
{
    /**
     * @return list<array{0: class-string<BackedEnum>, 1: bool}>
     */
    public static function enumDataProvider(): array
    {
        return [
            [IntegerEnumWithNoCases::class, false],
            [StringEnumWithNoCases::class,  true],
            [IntegerEnum::class,            false],
            [StringEnum::class,             true],
        ];
    }

    /**
     * @param class-string<BackedEnum> $enum
     */
    #[DataProvider('enumDataProvider')]
    public function testTheCorrectBackingTypeIsDetected(string $enum, bool $expect): void
    {
        $type = new BackedEnumValueType($enum);

        $reflection = new ReflectionProperty($type, 'isStringBacked');
        static::assertSame($expect, $reflection->getValue($type));
    }

    public function testReflectionFailsForANonEnumArgument(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('A BackedEnum enum-string is required');

        new BackedEnumValueType(self::class);
    }
}
