<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

/**
 * @covers \Psl\Arr\is_array
 */
class IsArrayTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testIsArray(bool $expected, $value): void
    {
        static::assertSame($expected, Arr\is_array($value));
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
                (fn () => yield 5)(),
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
