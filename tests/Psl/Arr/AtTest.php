<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Exception;

class AtTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testAt($expected, array $array, $key): void
    {
        static::assertSame($expected, Arr\at($array, $key));
    }

    public function provideData(): array
    {
        return [
            [5, ['a' => 4, 'b' => 5], 'b'],
            [0, [0, 1], 0],
            [1, [1, 2], 0],
            [null, [null], 0],
        ];
    }

    public function testAtThrowsForOutOfBoundKey(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Key (5) is out-of-bounds.');

        Arr\at([], 5);
    }
}
