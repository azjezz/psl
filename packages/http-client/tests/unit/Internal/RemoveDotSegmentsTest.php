<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit\Internal;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\HTTP\Client\Internal;

final class RemoveDotSegmentsTest extends TestCase
{
    #[DataProvider('dotSegmentProvider')]
    public function testRemoveDotSegments(string $input, string $expected): void
    {
        static::assertSame($expected, Internal\remove_dot_segments($input));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function dotSegmentProvider(): iterable
    {
        yield 'parent resolves to root' => ['/a/..', '/'];
        yield 'current dir removed' => ['/a/.', '/a'];
        yield 'nested parent resolves to root' => ['/a/b/../..', '/'];
        yield 'absolute path no dots' => ['/a/b/c', '/a/b/c'];
        yield 'single dot segment' => ['/a/./b', '/a/b'];
        yield 'double dot segment' => ['/a/b/../c', '/a/c'];
        yield 'empty path' => ['', ''];
        yield 'root only' => ['/', '/'];
    }
}
