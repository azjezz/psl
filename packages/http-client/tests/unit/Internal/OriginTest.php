<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit\Internal;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\HTTP\Client\Connection\Origin;
use Psl\URL;

final class OriginTest extends TestCase
{
    public function testFromHttpUrl(): void
    {
        $origin = Origin::fromUrl(URL\parse('http://example.com/path'));

        static::assertSame('http', $origin->scheme);
        static::assertSame('example.com', $origin->host);
        static::assertSame(80, $origin->port);
    }

    public function testFromHttpsUrl(): void
    {
        $origin = Origin::fromUrl(URL\parse('https://example.com/path'));

        static::assertSame('https', $origin->scheme);
        static::assertSame('example.com', $origin->host);
        static::assertSame(443, $origin->port);
    }

    public function testFromUrlWithExplicitPort(): void
    {
        $origin = Origin::fromUrl(URL\parse('http://example.com:9090/path'));

        static::assertSame(9090, $origin->port);
    }

    public function testToString(): void
    {
        $origin = new Origin('https', 'example.com', 443);

        static::assertSame('https://example.com:443', $origin->toString());
        static::assertSame('https://example.com:443', (string) $origin);
    }

    #[DataProvider('sameOriginProvider')]
    public function testSameOriginsProduceSameKey(string $url1, string $url2): void
    {
        $o1 = Origin::fromUrl(URL\parse($url1));
        $o2 = Origin::fromUrl(URL\parse($url2));

        static::assertSame($o1->toString(), $o2->toString());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function sameOriginProvider(): iterable
    {
        yield 'same http' => ['http://example.com/a', 'http://example.com/b'];
        yield 'implicit port' => ['http://example.com', 'http://example.com:80'];
        yield 'https implicit port' => ['https://example.com', 'https://example.com:443'];
    }

    #[DataProvider('differentOriginProvider')]
    public function testDifferentOriginsProduceDifferentKeys(string $url1, string $url2): void
    {
        $o1 = Origin::fromUrl(URL\parse($url1));
        $o2 = Origin::fromUrl(URL\parse($url2));

        static::assertNotSame($o1->toString(), $o2->toString());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function differentOriginProvider(): iterable
    {
        yield 'different scheme' => ['http://example.com', 'https://example.com'];
        yield 'different host' => ['http://a.com', 'http://b.com'];
        yield 'different port' => ['http://example.com:80', 'http://example.com:8080'];
    }

    public function testIpAddress(): void
    {
        $origin = Origin::fromUrl(URL\parse('http://127.0.0.1:8080/'));

        static::assertSame('127.0.0.1', $origin->host);
        static::assertSame(8080, $origin->port);
    }
}
