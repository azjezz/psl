<?php

declare(strict_types=1);

namespace Psl\Vec\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Vec;

final class DropTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testDrop(array $expected, array $array, int $n): void
    {
        $result = Vec\drop($array, $n);

        static::assertSame($expected, $result);
    }

    public static function provideData(): iterable
    {
        yield [[1, 2, 3], ['a' => 1, 'b' => 2, 'c' => 3], 0];
        yield [[4, 5], [1, 2, 3, 4, 5], 3];
        yield [[3, 4], ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], 2];
        yield [[], [1, 2, 3, 4, 5], 5];
    }
}
