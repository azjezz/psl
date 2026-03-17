<?php

declare(strict_types=1);

namespace Psl\URI\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\URI;
use Psl\URI\Authority\Authority;
use Psl\URI\Authority\RegisteredNameHost;
use Psl\URI\PathKind;

final class URITest extends TestCase
{
    public function testFullURI(): void
    {
        $uri = new URI\URI(
            scheme: 'http',
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: 'example.com'), port: null),
            path: '/path',
            pathKind: PathKind::Absolute,
            query: 'q',
            fragment: 'f',
        );

        static::assertSame('http://example.com/path?q#f', $uri->toString());
    }

    public function testSchemeWithRootlessPath(): void
    {
        $uri = new URI\URI(
            scheme: 'urn',
            authority: null,
            path: 'isbn:123',
            pathKind: PathKind::Rootless,
            query: null,
            fragment: null,
        );

        static::assertSame('urn:isbn:123', $uri->toString());
    }

    public function testAuthorityOnly(): void
    {
        $uri = new URI\URI(
            scheme: null,
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: 'example.com'), port: null),
            path: '/path',
            pathKind: PathKind::Absolute,
            query: null,
            fragment: null,
        );

        static::assertSame('//example.com/path', $uri->toString());
    }

    public function testRelativePath(): void
    {
        $uri = new URI\URI(
            scheme: null,
            authority: null,
            path: 'relative',
            pathKind: PathKind::Rootless,
            query: null,
            fragment: null,
        );

        static::assertSame('relative', $uri->toString());
    }

    public function testNullQueryOmitsQuestionMark(): void
    {
        $uri = new URI\URI(
            scheme: 'http',
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: 'h'), port: null),
            path: '',
            pathKind: PathKind::None,
            query: null,
            fragment: null,
        );

        static::assertSame('http://h', $uri->toString());
    }

    public function testEmptyQueryIncludesQuestionMark(): void
    {
        $uri = new URI\URI(
            scheme: 'http',
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: 'h'), port: null),
            path: '',
            pathKind: PathKind::None,
            query: '',
            fragment: null,
        );

        static::assertSame('http://h?', $uri->toString());
    }

    public function testNullFragmentOmitsHash(): void
    {
        $uri = new URI\URI(
            scheme: 'http',
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: 'h'), port: null),
            path: '',
            pathKind: PathKind::None,
            query: null,
            fragment: null,
        );

        static::assertSame('http://h', $uri->toString());
    }

    public function testEmptyFragmentIncludesHash(): void
    {
        $uri = new URI\URI(
            scheme: 'http',
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: 'h'), port: null),
            path: '',
            pathKind: PathKind::None,
            query: null,
            fragment: '',
        );

        static::assertSame('http://h#', $uri->toString());
    }

    public function testPathKindAbsolute(): void
    {
        $uri = new URI\URI(
            scheme: null,
            authority: null,
            path: '/absolute',
            pathKind: PathKind::Absolute,
            query: null,
            fragment: null,
        );

        static::assertSame(PathKind::Absolute, $uri->pathKind);
    }

    public function testPathKindRootless(): void
    {
        $uri = new URI\URI(
            scheme: null,
            authority: null,
            path: 'rootless',
            pathKind: PathKind::Rootless,
            query: null,
            fragment: null,
        );

        static::assertSame(PathKind::Rootless, $uri->pathKind);
    }

    public function testPathKindNone(): void
    {
        $uri = new URI\URI(
            scheme: null,
            authority: null,
            path: '',
            pathKind: PathKind::None,
            query: null,
            fragment: null,
        );

        static::assertSame(PathKind::None, $uri->pathKind);
    }

    public function testStringable(): void
    {
        $uri = new URI\URI(
            scheme: 'http',
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: 'example.com'), port: null),
            path: '/path',
            pathKind: PathKind::Absolute,
            query: 'q',
            fragment: 'f',
        );

        static::assertSame('http://example.com/path?q#f', (string) $uri);
    }

    public function testAllEmptyOptionalComponents(): void
    {
        $uri = new URI\URI(
            scheme: null,
            authority: null,
            path: '',
            pathKind: PathKind::None,
            query: null,
            fragment: null,
        );

        static::assertSame('', $uri->toString());
        static::assertNull($uri->scheme);
        static::assertNull($uri->authority);
        static::assertSame('', $uri->path);
        static::assertNull($uri->query);
        static::assertNull($uri->fragment);
    }

    public function testToStringRoundtripAfterParse(): void
    {
        $original = 'http://user:pass@example.com:8080/path?query=value#fragment';
        $uri = URI\parse($original);

        static::assertSame($original, $uri->toString());
    }

    public function testToStringRoundtripSimple(): void
    {
        $original = 'http://example.com/';
        $uri = URI\parse($original);

        static::assertSame($original, $uri->toString());
    }

    public function testToStringRoundtripWithEmptyComponents(): void
    {
        $original = 'http://h?#';
        $uri = URI\parse($original);

        static::assertSame($original, $uri->toString());
    }

    public function testAuthorityWithPortZero(): void
    {
        $uri = new URI\URI(
            scheme: 'http',
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: 'h'), port: 0),
            path: '/',
            pathKind: PathKind::Absolute,
            query: null,
            fragment: null,
        );

        static::assertSame('http://h:0/', $uri->toString());
    }

    public function testSchemeOnlyWithRootlessPath(): void
    {
        $uri = new URI\URI(
            scheme: 'mailto',
            authority: null,
            path: 'user@example.com',
            pathKind: PathKind::Rootless,
            query: null,
            fragment: null,
        );

        static::assertSame('mailto:user@example.com', $uri->toString());
    }

    public function testQueryOnlyNoFragment(): void
    {
        $uri = new URI\URI(
            scheme: null,
            authority: null,
            path: '',
            pathKind: PathKind::None,
            query: 'search',
            fragment: null,
        );

        static::assertSame('?search', $uri->toString());
    }

    public function testFragmentOnlyNoQuery(): void
    {
        $uri = new URI\URI(
            scheme: null,
            authority: null,
            path: '',
            pathKind: PathKind::None,
            query: null,
            fragment: 'section',
        );

        static::assertSame('#section', $uri->toString());
    }
}
