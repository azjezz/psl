<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;

class ToArrayTest extends TestCase
{
    /**
     * @dataProvider provideToArrayData
     */
    public function testToArray(iterable $iterable, array $expected): void
    {
        $result = Iter\to_array($iterable);

        self::assertSame($expected, $result);
    }

    public function provideToArrayData(): iterable
    {
        yield [[1, 2, 3], [1, 2, 3]];
        yield [[null], [null]];
        yield [['foo' => 'bar', 'baz' => 'qux'], ['bar', 'qux']];
        yield [[], []];
        yield [['hello', 'world'], ['hello', 'world']];

        yield [new Collection\Vector([1, 2, 3]), [1, 2, 3]];
        yield [new Collection\Vector([null]), [null]];
        yield [new Collection\Map(['foo' => 'bar', 'baz' => 'qux']), ['bar', 'qux']];
        yield [new Collection\Map([]), []];
        yield [new Collection\MutableVector(['hello', 'world']), ['hello', 'world']];

        yield [$this->createGeneratorFromIterable([1, 2, 3]), [1, 2, 3]];
        yield [$this->createGeneratorFromIterable([null]), [null]];
        yield [$this->createGeneratorFromIterable(['foo' => 'bar', 'baz' => 'qux']), ['bar', 'qux']];
        yield [$this->createGeneratorFromIterable([]), []];
        yield [$this->createGeneratorFromIterable(['hello', 'world']), ['hello', 'world']];
    }

    private function createGeneratorFromIterable(iterable $iterable): iterable
    {
        foreach ($iterable as $k => $v) {
            yield $k => $v;
        }
    }
}
