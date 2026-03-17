<?php

declare(strict_types=1);

namespace Psl\URL\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\IRI;
use Psl\URI\Authority\RegisteredNameHost;
use Psl\URL;
use Psl\URL\Exception\InvalidURLException;

final class FromIRITest extends TestCase
{
    public function testUnicodeIRIToURL(): void
    {
        $iri = IRI\parse('http://例え.jp/パス');

        $url = URL\from_iri($iri);

        static::assertSame('http', $url->scheme);
        static::assertInstanceOf(RegisteredNameHost::class, $url->authority->host);
        static::assertNotSame('例え.jp', $url->authority->host->name);
        static::assertStringStartsWith('xn--', $url->authority->host->name);
        static::assertNotSame('/パス', $url->path);
    }

    public function testASCIIIRIToURL(): void
    {
        $iri = IRI\parse('https://example.com/path');

        $url = URL\from_iri($iri);

        static::assertSame('https', $url->scheme);
        static::assertInstanceOf(RegisteredNameHost::class, $url->authority->host);
        static::assertSame('example.com', $url->authority->host->name);
        static::assertSame('/path', $url->path);
    }

    public function testDefaultPortStripped(): void
    {
        $iri = IRI\parse('http://例え.jp:80/');

        $url = URL\from_iri($iri);

        static::assertNull($url->authority->port);
    }

    public function testUnicodeHostConvertedToPunycode(): void
    {
        $iri = IRI\parse('https://münchen.de/');

        $url = URL\from_iri($iri);

        static::assertInstanceOf(RegisteredNameHost::class, $url->authority->host);
        static::assertStringStartsWith('xn--', $url->authority->host->name);
        static::assertStringNotContainsString('münchen', $url->authority->host->name);
    }

    public function testUnicodePathPercentEncoded(): void
    {
        $iri = IRI\parse('https://example.com/café');

        $url = URL\from_iri($iri);

        static::assertStringNotContainsString('é', $url->path);
        static::assertStringContainsString('caf', $url->path);
    }

    public function testUnicodeQueryPercentEncoded(): void
    {
        $iri = IRI\parse('https://example.com/?q=日本語');

        $url = URL\from_iri($iri);

        static::assertNotNull($url->query);
        static::assertStringNotContainsString('日本語', $url->query);
        static::assertStringContainsString('q=', $url->query);
    }

    public function testUnicodeFragmentPercentEncoded(): void
    {
        $iri = IRI\parse('https://example.com/#séction');

        $url = URL\from_iri($iri);

        static::assertNotNull($url->fragment);
        static::assertStringNotContainsString('é', $url->fragment);
    }

    public function testNonDefaultPortKept(): void
    {
        $iri = IRI\parse('http://例え.jp:8080/');

        $url = URL\from_iri($iri);

        static::assertSame(8080, $url->authority->port);
    }

    public function testMissingSchemeThrows(): void
    {
        $iri = IRI\parse('//example.com/path');

        $this->expectException(InvalidURLException::class);

        URL\from_iri($iri);
    }

    public function testMissingAuthorityThrows(): void
    {
        $iri = IRI\parse('urn:isbn:12345');

        $this->expectException(InvalidURLException::class);

        URL\from_iri($iri);
    }
}
