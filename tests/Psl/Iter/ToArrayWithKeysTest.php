<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Collection;

class ToArrayWithKeysTest extends TestCase
{
    /**
     * @dataProvider provideToArrayWithKeysData
     */
    public function testToArrayWithKeys(iterable $iterable, array $expected): void
    {
        $result = Iter\to_array_with_keys($iterable);

        self::assertSame($expected, $result);
    }

    public function provideToArrayWithKeysData(): iterable
    {
        yield [[1, 2, 3], [1, 2, 3]];
        yield [[null], [null]];
        yield [['foo' => 'bar', 'baz' => 'qux'], ['foo' => 'bar', 'baz' => 'qux']];
        yield [[], []];
        yield [['hello', 'world'], ['hello', 'world']];

        yield [new Collection\Vector([1, 2, 3]), [1, 2, 3]];
        yield [new Collection\Vector([null]), [null]];
        yield [new Collection\Map(['foo' => 'bar', 'baz' => 'qux']), ['foo' => 'bar', 'baz' => 'qux']];
        yield [new Collection\Map([]), []];
        yield [new Collection\MutableVector(['hello', 'world']), ['hello', 'world']];

        yield [$this->createGeneratorFromIterable([1, 2, 3]), [1, 2, 3]];
        yield [$this->createGeneratorFromIterable([null]), [null]];
        yield [$this->createGeneratorFromIterable(['foo' => 'bar', 'baz' => 'qux']), ['foo' => 'bar', 'baz' => 'qux']];
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
