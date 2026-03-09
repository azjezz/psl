<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Dict;
use Psl\Iter;

final class ToIteratorTest extends TestCase
{
    /**
     * @param array<array-key, mixed> $array
     */
    #[DataProvider('provideToIteratorData')]
    public function testToIterator(array $array): void
    {
        $iterator = Iter\to_iterator($array);

        static::assertCount(Iter\count($array), $iterator);
        static::assertSame($array, Dict\from_iterable($iterator));
    }

    /**
     * @return iterable<array{0: array<array-key, mixed>}>
     */
    public static function provideToIteratorData(): iterable
    {
        yield [[1, 2, 3]];
        yield [[null]];
        yield [['foo' => 'bar', 'baz' => 'qux']];
        yield [[]];
        yield [['hello', 'world']];
    }
}
