<?php

declare(strict_types=1);

namespace Psl\Tests\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Vec;

final class MapTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMap(array $expected, array $array, callable $function): void
    {
        $result = Vec\map($array, $function);

        static::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield [[1, 2, 3], [1, 2, 3], static fn (int $v): int => $v];
        yield [[2, 4, 6], [1, 2, 3], static fn (int $v): int => $v * 2];
        yield [['1', '2', '3'], [1, 2, 3], static fn (int $v): string => (string)$v];
        yield [[], [], static fn (int $v): string => (string)$v];
    }
}
