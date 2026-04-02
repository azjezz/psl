<?php

declare(strict_types=1);

namespace Psl\HPACK\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\HPACK\Exception\DecodingException;
use Psl\HPACK\Internal\Huffman;

use function chr;
use function hex2bin;
use function strlen;
use function substr;

final class HuffmanTest extends TestCase
{
    public function testEncodeWwwExampleCom(): void
    {
        $encoded = Huffman::encode('www.example.com');

        static::assertSame(hex2bin('f1e3c2e5f23a6ba0ab90f4ff'), $encoded);
    }

    public function testDecodeWwwExampleCom(): void
    {
        $decoded = Huffman::decode(hex2bin('f1e3c2e5f23a6ba0ab90f4ff'));

        static::assertSame('www.example.com', $decoded);
    }

    public function testEncodeNoCache(): void
    {
        $encoded = Huffman::encode('no-cache');

        static::assertSame(hex2bin('a8eb10649cbf'), $encoded);
    }

    public function testDecodeNoCache(): void
    {
        $decoded = Huffman::decode(hex2bin('a8eb10649cbf'));

        static::assertSame('no-cache', $decoded);
    }

    public function testEncodeCustomKey(): void
    {
        $encoded = Huffman::encode('custom-key');

        static::assertSame(hex2bin('25a849e95ba97d7f'), $encoded);
    }

    public function testDecodeCustomKey(): void
    {
        $decoded = Huffman::decode(hex2bin('25a849e95ba97d7f'));

        static::assertSame('custom-key', $decoded);
    }

    public function testEncodeCustomValue(): void
    {
        $encoded = Huffman::encode('custom-value');

        static::assertSame(hex2bin('25a849e95bb8e8b4bf'), $encoded);
    }

    public function testDecodeCustomValue(): void
    {
        $decoded = Huffman::decode(hex2bin('25a849e95bb8e8b4bf'));

        static::assertSame('custom-value', $decoded);
    }

    public function testEmptyString(): void
    {
        $encoded = Huffman::encode('');

        static::assertSame('', $encoded);

        $decoded = Huffman::decode('');

        static::assertSame('', $decoded);
    }

    public function testAllSingleByteValuesRoundTrip(): void
    {
        for ($i = 0; $i < 256; $i++) {
            $char = chr($i);
            $encoded = Huffman::encode($char);
            $decoded = Huffman::decode($encoded);
            static::assertSame($char, $decoded);
        }
    }

    public function testRoundTripVariousStrings(): void
    {
        $strings = [
            'hello',
            'Hello World!',
            ':method',
            'GET',
            '/index.html',
            'text/html; charset=utf-8',
            'Mon, 21 Oct 2013 20:13:21 GMT',
        ];

        foreach ($strings as $string) {
            $encoded = Huffman::encode($string);
            $decoded = Huffman::decode($encoded);
            static::assertSame($string, $decoded);
        }
    }

    public function testHuffmanEncodingIsShorter(): void
    {
        $input = 'www.example.com';
        $encoded = Huffman::encode($input);

        static::assertLessThan(strlen($input), strlen($encoded));
    }

    public function testEstimateEncodedBitsMatchesActualEncoding(): void
    {
        $strings = [
            'www.example.com',
            'no-cache',
            'GET',
            '/index.html',
            'text/html; charset=utf-8',
            'authorization',
            'cookie',
        ];

        foreach ($strings as $string) {
            $estimatedBits = Huffman::estimateEncodedBits($string, strlen($string));
            $encoded = Huffman::encode($string);
            $actualBits = strlen($encoded) * 8;

            static::assertLessThanOrEqual(7, $actualBits - $estimatedBits, "Estimate too low for '$string'");
            static::assertGreaterThanOrEqual(0, $actualBits - $estimatedBits, "Estimate too high for '$string'");
        }
    }

    public function testDecodeTruncatedData(): void
    {
        $this->expectException(DecodingException::class);

        $fullEncoded = Huffman::encode('www.example.com');
        /** @var non-negative-int $truncatedLength */
        $truncatedLength = strlen($fullEncoded) - 2;
        Huffman::decode(substr($fullEncoded, 0, $truncatedLength));
    }
}
