<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

class EnumerateTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testEnumerate(array $expected, iterable $iterable): void
    {
        $result = Iter\enumerate($iterable);

        self::assertSame($expected, Iter\to_array_with_keys($result));
    }

    public function provideData(): iterable
    {
        yield [[], []];
        yield [[['a', 'b'], ['c', 'd']], ['a' => 'b', 'c' => 'd']];
        yield [[['a', 'b'], ['a', 'b'], ['a', 'b']], (static function () {
            yield 'a' => 'b';
            yield 'a' => 'b';
            yield 'a' => 'b';
        })()];
        yield [[['a', null], ['b', 0]], ['a' => null, 'b' => 0]];
    }
}
