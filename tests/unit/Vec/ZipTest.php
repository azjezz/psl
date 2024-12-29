<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Vec;

final class ZipTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testZip(array $expected, array $first, array $second): void
    {
        static::assertSame($expected, Vec\zip($first, $second));
    }

    public function provideData(): iterable
    {
        yield [
            [['foo', 'baz'], ['bar', 'qux']],
            ['foo', 'bar'],
            ['baz', 'qux'],
        ];

        yield [
            [],
            [],
            [],
        ];

        yield [
            [],
            ['foo', 'bar'],
            [],
        ];
    }
}
