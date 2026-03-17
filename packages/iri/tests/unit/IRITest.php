<?php

declare(strict_types=1);

namespace Psl\IRI\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\IRI;
use Psl\URI\Authority\Authority;
use Psl\URI\Authority\RegisteredNameHost;
use Psl\URI\PathKind;

final class IRITest extends TestCase
{
    public function testToStringPreservesUnicode(): void
    {
        $iri = new IRI\IRI(
            scheme: 'http',
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: '例え.jp'), port: null),
            path: '/パス',
            pathKind: PathKind::Absolute,
            query: 'q=日本語',
            fragment: 'フラグ',
        );

        static::assertSame('http://例え.jp/パス?q=日本語#フラグ', $iri->toString());
    }

    public function testToURIPunycodeEncodesHostAndPercentEncodesComponents(): void
    {
        $iri = new IRI\IRI(
            scheme: 'http',
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: '例え.jp'), port: null),
            path: '/パス',
            pathKind: PathKind::Absolute,
            query: 'q=日本語',
            fragment: 'フラグ',
        );

        $uri = $iri->toURI();

        static::assertNotNull($uri->authority);
        static::assertInstanceOf(RegisteredNameHost::class, $uri->authority->host);
        static::assertSame('xn--r8jz45g.jp', $uri->authority->host->name);
        static::assertSame('/%E3%83%91%E3%82%B9', $uri->path);
        static::assertSame('q=%E6%97%A5%E6%9C%AC%E8%AA%9E', $uri->query);
        static::assertSame('%E3%83%95%E3%83%A9%E3%82%B0', $uri->fragment);
    }

    public function testRoundTrip(): void
    {
        $original = 'http://例え.jp/パス?q=日本語#フラグ';
        $iri = IRI\parse($original);
        $uri = $iri->toURI();
        $roundTripped = IRI\from_uri($uri);

        static::assertSame($original, $roundTripped->toString());
    }

    public function testStringable(): void
    {
        $iri = new IRI\IRI(
            scheme: 'http',
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: '例え.jp'), port: null),
            path: '/パス',
            pathKind: PathKind::Absolute,
            query: 'q=日本語',
            fragment: 'フラグ',
        );

        static::assertSame('http://例え.jp/パス?q=日本語#フラグ', (string) $iri);
    }

    public function testToURIWithASCIIOnlyIRI(): void
    {
        $iri = new IRI\IRI(
            scheme: 'http',
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: 'example.com'), port: null),
            path: '/path',
            pathKind: PathKind::Absolute,
            query: 'q=1',
            fragment: 'frag',
        );

        $uri = $iri->toURI();

        $authority = $uri->authority;
        static::assertNotNull($authority);
        static::assertInstanceOf(RegisteredNameHost::class, $authority->host);
        static::assertSame('example.com', $authority->host->name);
        static::assertSame('/path', $uri->path);
        static::assertSame('q=1', $uri->query);
        static::assertSame('frag', $uri->fragment);
    }

    public function testToURIWithUnicodeInQueryOnly(): void
    {
        $iri = new IRI\IRI(
            scheme: 'http',
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: 'example.com'), port: null),
            path: '/path',
            pathKind: PathKind::Absolute,
            query: 'q=日本語',
            fragment: null,
        );

        $uri = $iri->toURI();

        static::assertSame('/path', $uri->path);
        static::assertSame('q=%E6%97%A5%E6%9C%AC%E8%AA%9E', $uri->query);
        static::assertNull($uri->fragment);
    }

    public function testToURIWithUnicodeInFragmentOnly(): void
    {
        $iri = new IRI\IRI(
            scheme: 'http',
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: 'example.com'), port: null),
            path: '/path',
            pathKind: PathKind::Absolute,
            query: null,
            fragment: 'フラグ',
        );

        $uri = $iri->toURI();

        static::assertSame('/path', $uri->path);
        static::assertNull($uri->query);
        static::assertSame('%E3%83%95%E3%83%A9%E3%82%B0', $uri->fragment);
    }

    public function testToURIPreservesPortUserInfoAndScheme(): void
    {
        $iri = new IRI\IRI(
            scheme: 'https',
            authority: new Authority(userInfo: 'user:pass', host: new RegisteredNameHost(name: '例え.jp'), port: 8443),
            path: '/パス',
            pathKind: PathKind::Absolute,
            query: null,
            fragment: null,
        );

        $uri = $iri->toURI();

        static::assertSame('https', $uri->scheme);
        $authority = $uri->authority;
        static::assertNotNull($authority);
        static::assertSame('user:pass', $authority->userInfo);
        static::assertSame(8443, $authority->port);
        static::assertInstanceOf(RegisteredNameHost::class, $authority->host);
        static::assertSame('xn--r8jz45g.jp', $authority->host->name);
    }

    public function testToStringWithNullAuthority(): void
    {
        $iri = new IRI\IRI(
            scheme: 'urn',
            authority: null,
            path: 'isbn:0451450523',
            pathKind: PathKind::Rootless,
            query: null,
            fragment: null,
        );

        static::assertSame('urn:isbn:0451450523', $iri->toString());
    }

    public function testToStringWithEmptyPath(): void
    {
        $iri = new IRI\IRI(
            scheme: 'http',
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: 'example.com'), port: null),
            path: '',
            pathKind: PathKind::None,
            query: 'q=1',
            fragment: null,
        );

        static::assertSame('http://example.com?q=1', $iri->toString());
    }

    public function testToStringWithEmptyQueryAndFragment(): void
    {
        $iri = new IRI\IRI(
            scheme: 'http',
            authority: new Authority(userInfo: null, host: new RegisteredNameHost(name: 'example.com'), port: null),
            path: '/',
            pathKind: PathKind::Absolute,
            query: '',
            fragment: '',
        );

        static::assertSame('http://example.com/?#', $iri->toString());
    }

    public function testToURIWithUnicodeUserInfo(): void
    {
        $iri = new IRI\IRI(
            scheme: 'http',
            authority: new Authority(
                userInfo: 'пользователь',
                host: new RegisteredNameHost(name: 'example.com'),
                port: null,
            ),
            path: '/',
            pathKind: PathKind::Absolute,
            query: null,
            fragment: null,
        );

        $uri = $iri->toURI();

        $authority = $uri->authority;
        static::assertNotNull($authority);
        static::assertSame(
            '%D0%BF%D0%BE%D0%BB%D1%8C%D0%B7%D0%BE%D0%B2%D0%B0%D1%82%D0%B5%D0%BB%D1%8C',
            $authority->userInfo,
        );
    }

    public function testToURIWithNullAuthority(): void
    {
        $iri = new IRI\IRI(
            scheme: 'mailto',
            authority: null,
            path: 'user@example.com',
            pathKind: PathKind::Rootless,
            query: null,
            fragment: null,
        );

        $uri = $iri->toURI();

        static::assertNull($uri->authority);
        static::assertSame('mailto', $uri->scheme);
        static::assertSame('user@example.com', $uri->path);
    }
}
