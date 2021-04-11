<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class ToIteratorTest extends TestCase
{
    /**
     * @dataProvider provideToIteratorData
     */
    public function testToIterator(array $array): void
    {
        $iterator = Iter\to_iterator($array);

        static::assertCount(Iter\count($array), $iterator);
        static::assertSame($array, Iter\to_array_with_keys($iterator));
    }

    public function provideToIteratorData(): iterable
    {
        yield [[1, 2, 3]];
        yield [[null]];
        yield [['foo' => 'bar', 'baz' => 'qux']];
        yield [[]];
        yield [['hello', 'world']];
    }
}
