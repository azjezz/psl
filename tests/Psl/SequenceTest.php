<?php

declare(strict_types=1);

namespace Psl\Tests;

use PHPUnit\Framework\TestCase;
use Psl;

class SequenceTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSequence(array $args, $expected): void
    {
        self::assertSame($expected, Psl\sequence(...$args));
    }

    public function provideData(): iterable
    {
        yield [[], null];
        yield [[null], null];
        yield [[1, 2, 3], 3];
        yield [['foo', 'bar', 'baz', 'qux'], 'qux'];
    }
}
