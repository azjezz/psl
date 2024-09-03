<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type\Internal;

use BackedEnum;
use PHPUnit\Framework\TestCase;
use Psl\Exception\InvariantViolationException;
use Psl\Tests\Fixture\IntegerEnum;
use Psl\Tests\Fixture\IntegerEnumWithNoCases;
use Psl\Tests\Fixture\StringEnum;
use Psl\Tests\Fixture\StringEnumWithNoCases;
use Psl\Type\Internal\BackedEnumValueType;
use ReflectionProperty;

class BackedEnumValueTypeTest extends TestCase
{
    /**
     * @return list<array{0: class-string<BackedEnum>, 1: bool}
     */
    public static function enumDataProvider(): array
    {
        return [
            [IntegerEnumWithNoCases::class, false],
            [StringEnumWithNoCases::class, true],
            [IntegerEnum::class, false],
            [StringEnum::class, true],
        ];
    }

    /**
     * @dataProvider enumDataProvider
     *
     * @param class-string<BackedEnum> $enum
     */
    public function testTheCorrectBackingTypeIsDetected(string $enum, bool $expect): void
    {
        $type = new BackedEnumValueType($enum);

        $reflection = new ReflectionProperty($type, 'isStringBacked');
        static::assertSame($expect, $reflection->getValue($type));
    }

    public function testReflectionFailsForANonEnumArgument(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('A BackedEnum class-string is required');

        new BackedEnumValueType(self::class);
    }
}
