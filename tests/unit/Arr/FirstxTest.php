<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Exception;
use Psl\Iter;

final class FirstxTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFirstx($expected, array $array): void
    {
        static::assertSame($expected, Arr\firstx($array));
    }

    public function provideData(): array
    {
        return [
            [
                0,
                Iter\to_array(Iter\range(0, 10)),
            ],

            [
                null,
                [null],
            ],
        ];
    }

    public function testFirstxThrowsForEmptyArray(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Expected a non-empty array.');

        Arr\firstx([]);
    }
}
