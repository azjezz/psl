<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit\Internal;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function Psl\HTTP\Client\Internal\merge_paths;

final class MergePathsTest extends TestCase
{
    #[DataProvider('mergePathsProvider')]
    public function testMergePaths(string $basePath, string $relativePath, string $expected): void
    {
        static::assertSame($expected, merge_paths($basePath, $relativePath));
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function mergePathsProvider(): iterable
    {
        yield 'empty relative returns base' => ['/a/b', '', '/a/b'];
        yield 'empty base prefixes with slash' => ['', 'path', '/path'];
        yield 'normal merge replaces last segment' => ['/a/b', 'c', '/a/c'];
        yield 'base with trailing slash' => ['/a/b/', 'c', '/a/b/c'];
        yield 'root base' => ['/', 'path', '/path'];
    }
}
