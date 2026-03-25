<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\URL;

use function Psl\HTTP\Client\Internal\resolve_url;

final class ResolveUrlTest extends TestCase
{
    public function testAbsoluteReferenceReturnsAsIs(): void
    {
        $base = URL\parse('https://example.com/old/path?q=1#f');

        $result = resolve_url('https://other.com/new/path?x=2#y', $base);

        static::assertSame('https://other.com/new/path?x=2#y', $result->toString());
    }

    public function testEmptyPathPreservesBasePath(): void
    {
        $base = URL\parse('https://example.com/old/path?oldquery');

        $result = resolve_url('?newquery', $base);

        static::assertSame('https', $result->scheme);
        static::assertSame('/old/path', $result->path);
        static::assertSame('newquery', $result->query);
        static::assertNull($result->fragment);
    }

    public function testEmptyPathAndQueryPreservesBase(): void
    {
        $base = URL\parse('https://example.com/old/path?oldquery');

        $result = resolve_url('#frag', $base);

        static::assertSame('https', $result->scheme);
        static::assertSame('/old/path', $result->path);
        static::assertSame('oldquery', $result->query);
        static::assertSame('frag', $result->fragment);
    }

    public function testAbsolutePathReference(): void
    {
        $base = URL\parse('https://example.com/old/path?q=1');

        $result = resolve_url('/new/path', $base);

        static::assertSame('https', $result->scheme);
        static::assertSame('example.com', $result->authority->toString());
        static::assertSame('/new/path', $result->path);
        static::assertNull($result->query);
        static::assertNull($result->fragment);
    }

    public function testRelativePathMerged(): void
    {
        $base = URL\parse('https://example.com/a/b');

        $result = resolve_url('relative/path', $base);

        static::assertSame('https', $result->scheme);
        static::assertSame('example.com', $result->authority->toString());
        static::assertSame('/a/relative/path', $result->path);
        static::assertNull($result->query);
    }

    public function testDotSegmentsRemoved(): void
    {
        $base = URL\parse('https://example.com/a/b/');

        $result = resolve_url('../sibling', $base);

        static::assertSame('/a/sibling', $result->path);
    }

    public function testFragmentStrippedFromReference(): void
    {
        $base = URL\parse('https://example.com/base');

        $result = resolve_url('/new/path#section', $base);

        static::assertSame('/new/path', $result->path);
        static::assertSame('section', $result->fragment);
        static::assertNull($result->query);
    }

    public function testQueryAndFragmentOnRelativePath(): void
    {
        $base = URL\parse('https://example.com/a/b');

        $result = resolve_url('c?key=val#frag', $base);

        static::assertSame('/a/c', $result->path);
        static::assertSame('key=val', $result->query);
        static::assertSame('frag', $result->fragment);
    }
}
