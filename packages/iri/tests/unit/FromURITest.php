<?php

declare(strict_types=1);

namespace Psl\IRI\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\IRI;
use Psl\URI;

final class FromURITest extends TestCase
{
    public function testPercentDecodedUnicode(): void
    {
        $uri = URI\parse('http://example.com/%E3%83%91%E3%82%B9');
        $iri = IRI\from_uri($uri);

        static::assertSame('/パス', $iri->path);
    }

    public function testPunycodeToUnicode(): void
    {
        $uri = URI\parse('http://xn--r8jz45g.jp/');
        $iri = IRI\from_uri($uri);

        static::assertNotNull($iri->authority);
        static::assertInstanceOf(URI\Authority\RegisteredNameHost::class, $iri->authority->host);
        static::assertSame('例え.jp', $iri->authority->host->name);
    }

    public function testASCIIPassthrough(): void
    {
        $uri = URI\parse('http://example.com/path');
        $iri = IRI\from_uri($uri);

        static::assertSame('/path', $iri->path);
    }

    public function testFullRoundTrip(): void
    {
        $original = 'http://例え.jp/パス?q=日本語#フラグ';
        $iri = IRI\parse($original);
        $uri = $iri->toURI();
        $roundTripped = IRI\from_uri($uri);

        static::assertSame($original, $roundTripped->toString());
    }

    public function testPureASCIIStaysASCII(): void
    {
        $uri = URI\parse('http://example.com/path?q=value#frag');
        $iri = IRI\from_uri($uri);

        static::assertSame('/path', $iri->path);
        static::assertSame('q=value', $iri->query);
        static::assertSame('frag', $iri->fragment);
        $authority = $iri->authority;
        static::assertNotNull($authority);
        static::assertInstanceOf(URI\Authority\RegisteredNameHost::class, $authority->host);
        static::assertSame('example.com', $authority->host->name);
    }

    public function testPercentEncodedMultiByteUTF8(): void
    {
        $uri = URI\parse('http://example.com/%E4%B8%AD%E6%96%87');
        $iri = IRI\from_uri($uri);

        static::assertSame('/中文', $iri->path);
    }

    public function testMixedEncodedAndUnencoded(): void
    {
        $uri = URI\parse('http://example.com/hello%E4%B8%96%E7%95%8C');
        $iri = IRI\from_uri($uri);

        static::assertSame('/hello世界', $iri->path);
    }

    public function testPunycodeWithPercentEncodedPath(): void
    {
        $uri = URI\parse('http://xn--r8jz45g.jp/%E3%83%91%E3%82%B9');
        $iri = IRI\from_uri($uri);

        $authority = $iri->authority;
        static::assertNotNull($authority);
        static::assertInstanceOf(URI\Authority\RegisteredNameHost::class, $authority->host);
        static::assertSame('例え.jp', $authority->host->name);
        static::assertSame('/パス', $iri->path);
    }

    public function testURIWithNoAuthority(): void
    {
        $uri = URI\parse('urn:isbn:0451450523');
        $iri = IRI\from_uri($uri);

        static::assertNull($iri->authority);
        static::assertSame('urn', $iri->scheme);
        static::assertSame('isbn:0451450523', $iri->path);
    }

    public function testPercentEncodedQuery(): void
    {
        $uri = URI\parse('http://example.com/?q=%E6%97%A5%E6%9C%AC%E8%AA%9E');
        $iri = IRI\from_uri($uri);

        static::assertSame('q=日本語', $iri->query);
    }

    public function testPercentEncodedFragment(): void
    {
        $uri = URI\parse('http://example.com/#%E3%83%95%E3%83%A9%E3%82%B0');
        $iri = IRI\from_uri($uri);

        static::assertSame('フラグ', $iri->fragment);
    }

    public function testASCIIPercentEncodingPreserved(): void
    {
        $uri = URI\parse('http://example.com/hello%20world');
        $iri = IRI\from_uri($uri);

        static::assertSame('/hello%20world', $iri->path);
    }

    public function testPercentEncodedUserInfo(): void
    {
        $uri = URI\parse(
            'http://%D0%BF%D0%BE%D0%BB%D1%8C%D0%B7%D0%BE%D0%B2%D0%B0%D1%82%D0%B5%D0%BB%D1%8C@example.com/',
        );
        $iri = IRI\from_uri($uri);

        $authority = $iri->authority;
        static::assertNotNull($authority);
        static::assertSame('пользователь', $authority->userInfo);
    }
}
