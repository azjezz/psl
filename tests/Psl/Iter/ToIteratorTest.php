<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

class ToIteratorTest extends TestCase
{
    /**
     * @dataProvider provideToIteratorData
     */
    public function testToIterator(array $array): void
    {
        $iterator = Iter\to_iterator($array);

        self::assertCount(Iter\count($array), $iterator);
        self::assertSame($array, Iter\to_array_with_keys($iterator));
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
