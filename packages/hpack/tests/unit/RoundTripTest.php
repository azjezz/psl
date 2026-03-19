<?php

declare(strict_types=1);

namespace Psl\HPACK\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\HPACK\Decoder;
use Psl\HPACK\Encoder;
use Psl\HPACK\Header;

use function count;
use function str_repeat;

final class RoundTripTest extends TestCase
{
    public function testSimpleHeaders(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $headers = [
            new Header(':method', 'GET'),
            new Header(':scheme', 'https'),
            new Header(':path', '/'),
            new Header(':authority', 'example.com'),
        ];

        $encoded = $encoder->encode($headers);
        $decoded = $decoder->decode($encoded);

        static::assertCount(4, $decoded);
        foreach ($headers as $i => $header) {
            static::assertSame($header->name, $decoded[$i]->name);
            static::assertSame($header->value, $decoded[$i]->value);
        }
    }

    public function testMultiBlockStateful(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $blocks = [
            [
                new Header(':method', 'GET'),
                new Header(':scheme', 'http'),
                new Header(':path', '/'),
                new Header(':authority', 'www.example.com'),
            ],
            [
                new Header(':method', 'GET'),
                new Header(':scheme', 'http'),
                new Header(':path', '/'),
                new Header(':authority', 'www.example.com'),
                new Header('cache-control', 'no-cache'),
            ],
            [
                new Header(':method', 'GET'),
                new Header(':scheme', 'https'),
                new Header(':path', '/index.html'),
                new Header(':authority', 'www.example.com'),
                new Header('custom-key', 'custom-value'),
            ],
        ];

        foreach ($blocks as $headers) {
            $encoded = $encoder->encode($headers);
            $decoded = $decoder->decode($encoded);

            static::assertCount(count($headers), $decoded);
            foreach ($headers as $i => $header) {
                static::assertSame($header->name, $decoded[$i]->name);
                static::assertSame($header->value, $decoded[$i]->value);
            }
        }
    }

    public function testLargeHeaderValues(): void
    {
        $encoder = new Encoder(65_536, 1_000_000);
        $decoder = new Decoder(65_536, 1_000_000);

        $largeValue = str_repeat('x', 65_536);
        $headers = [
            new Header('x-large', $largeValue),
        ];

        $encoded = $encoder->encode($headers);
        $decoded = $decoder->decode($encoded);

        static::assertCount(1, $decoded);
        static::assertSame('x-large', $decoded[0]->name);
        static::assertSame($largeValue, $decoded[0]->value);
    }

    public function testResizeRoundTrip(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $encoder->encode([
            new Header('custom-key', 'custom-value'),
        ]);
        $decoder->decode($encoder->encode([
            new Header(':method', 'GET'),
        ]));

        $encoder->resize(256);
        $decoder->resize(256);

        $headers = [
            new Header(':method', 'POST'),
            new Header(':path', '/api'),
        ];

        $encoded = $encoder->encode($headers);
        $decoded = $decoder->decode($encoded);

        static::assertCount(2, $decoded);
        static::assertSame(':method', $decoded[0]->name);
        static::assertSame('POST', $decoded[0]->value);
        static::assertSame(':path', $decoded[1]->name);
        static::assertSame('/api', $decoded[1]->value);
    }

    public function testSensitiveHeadersPreserved(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $headers = [
            new Header(':method', 'GET'),
            new Header('authorization', 'Bearer token123', true),
            new Header(':path', '/'),
        ];

        $encoded = $encoder->encode($headers);
        $decoded = $decoder->decode($encoded);

        static::assertCount(3, $decoded);
        static::assertFalse($decoded[0]->sensitive);
        static::assertTrue($decoded[1]->sensitive);
        static::assertFalse($decoded[2]->sensitive);
    }

    public function testMixedSensitiveAndNonSensitive(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $headers = [
            new Header(':method', 'POST'),
            new Header('content-type', 'application/json'),
            new Header('authorization', 'Bearer secret', true),
            new Header('x-request-id', '12345'),
            new Header('cookie', 'session=abc', true),
        ];

        $encoded = $encoder->encode($headers);
        $decoded = $decoder->decode($encoded);

        static::assertCount(5, $decoded);
        static::assertSame('authorization', $decoded[2]->name);
        static::assertTrue($decoded[2]->sensitive);
        static::assertSame('cookie', $decoded[4]->name);
        static::assertTrue($decoded[4]->sensitive);
    }

    public function testManyHeaders(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder(4096, 1_000_000);

        $headers = [];
        for ($i = 0; $i < 100; $i++) {
            $headers[] = new Header('x-header-' . $i, 'value-' . $i);
        }

        $encoded = $encoder->encode($headers);
        $decoded = $decoder->decode($encoded);

        static::assertCount(100, $decoded);
        foreach ($headers as $i => $header) {
            static::assertSame($header->name, $decoded[$i]->name);
            static::assertSame($header->value, $decoded[$i]->value);
        }
    }

    public function testEmptyHeaderValues(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $headers = [
            new Header(':method', 'GET'),
            new Header('x-empty', ''),
            new Header(':path', '/'),
        ];

        $encoded = $encoder->encode($headers);
        $decoded = $decoder->decode($encoded);

        static::assertCount(3, $decoded);
        static::assertSame('', $decoded[1]->value);
    }

    public function testResizeToZeroAndBack(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $encoder->encode([new Header('custom-key', 'custom-value')]);

        $encoder->resize(0);
        $decoder->resize(0);

        $encoder->resize(4096);
        $decoder->resize(4096);

        $headers = [
            new Header(':method', 'GET'),
            new Header(':path', '/'),
        ];

        $encoded = $encoder->encode($headers);
        $decoded = $decoder->decode($encoded);

        static::assertCount(2, $decoded);
        static::assertSame(':method', $decoded[0]->name);
        static::assertSame('GET', $decoded[0]->value);
    }
}
