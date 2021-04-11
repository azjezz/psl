<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use PHPUnit\Framework\TestCase;
use Psl\Type;

final class IsArrayTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testIsArray(bool $expected, $value): void
    {
        static::assertSame($expected, Type\is_array($value));
    }

    public function provideData(): array
    {
        return [
            [
                false,
                'foo',
            ],

            [
                true,
                [],
            ],

            [
                false,
                (static fn () => yield 5)(),
            ],

            [
                false,
                4,
            ],

            [
                false,
                false,
            ],

            [
                false,
                STDOUT,
            ],
        ];
    }
}
