<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Exception;
use Psl\Iter;

class LastxTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testLastx($expected, array $array): void
    {
        static::assertSame($expected, Arr\lastx($array));
    }

    public function provideData(): array
    {
        return [
            [
                10,
                Iter\to_array(Iter\range(0, 10)),
            ],

            [
                null,
                [null],
            ],
        ];
    }

    public function testLastxThrowsForEmptyArray(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Expected a non-empty array.');

        Arr\lastx([]);
    }
}
