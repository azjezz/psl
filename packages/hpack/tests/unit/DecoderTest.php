<?php

declare(strict_types=1);

namespace Psl\HPACK\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\HPACK\Decoder;
use Psl\HPACK\Exception\DecodingException;
use Psl\HPACK\Exception\HeaderListSizeException;
use Psl\HPACK\Exception\IntegerOverflowException;
use Psl\HPACK\Exception\InvalidTableIndexException;

use function hex2bin;

final class DecoderTest extends TestCase
{
    public function testEmptyBlock(): void
    {
        $decoder = new Decoder();

        static::assertSame([], $decoder->decode(''));
    }

    public function testRfcC3Request1WithoutHuffman(): void
    {
        $decoder = new Decoder();

        $headers = $decoder->decode(hex2bin('400a637573746f6d2d6b65790d637573746f6d2d686561646572'));

        static::assertCount(1, $headers);
        static::assertSame('custom-key', $headers[0]->name);
        static::assertSame('custom-header', $headers[0]->value);
    }

    public function testRfcC3Request2WithoutHuffman(): void
    {
        $decoder = new Decoder();

        $decoder->decode(hex2bin('400a637573746f6d2d6b65790d637573746f6d2d686561646572'));

        $headers = $decoder->decode(hex2bin('040c2f73616d706c652f70617468'));

        static::assertCount(1, $headers);
        static::assertSame(':path', $headers[0]->name);
        static::assertSame('/sample/path', $headers[0]->value);
    }

    public function testRfcC3Request3WithoutHuffman(): void
    {
        $decoder = new Decoder();

        $decoder->decode(hex2bin('400a637573746f6d2d6b65790d637573746f6d2d686561646572'));
        $decoder->decode(hex2bin('040c2f73616d706c652f70617468'));

        $headers = $decoder->decode(hex2bin('100870617373776f726406736563726574'));

        static::assertCount(1, $headers);
        static::assertSame('password', $headers[0]->name);
        static::assertSame('secret', $headers[0]->value);
        static::assertTrue($headers[0]->sensitive);
    }

    public function testRfcC4Request1WithHuffman(): void
    {
        $decoder = new Decoder();

        $headers = $decoder->decode(hex2bin('828684418cf1e3c2e5f23a6ba0ab90f4ff'));

        static::assertCount(4, $headers);
        static::assertSame(':method', $headers[0]->name);
        static::assertSame('GET', $headers[0]->value);
        static::assertSame(':scheme', $headers[1]->name);
        static::assertSame('http', $headers[1]->value);
        static::assertSame(':path', $headers[2]->name);
        static::assertSame('/', $headers[2]->value);
        static::assertSame(':authority', $headers[3]->name);
        static::assertSame('www.example.com', $headers[3]->value);
    }

    public function testRfcC4Request2WithHuffman(): void
    {
        $decoder = new Decoder();

        $decoder->decode(hex2bin('828684418cf1e3c2e5f23a6ba0ab90f4ff'));

        $headers = $decoder->decode(hex2bin('828684be5886a8eb10649cbf'));

        static::assertCount(5, $headers);
        static::assertSame(':method', $headers[0]->name);
        static::assertSame('GET', $headers[0]->value);
        static::assertSame(':authority', $headers[3]->name);
        static::assertSame('www.example.com', $headers[3]->value);
        static::assertSame('cache-control', $headers[4]->name);
        static::assertSame('no-cache', $headers[4]->value);
    }

    public function testRfcC4Request3WithHuffman(): void
    {
        $decoder = new Decoder();

        $decoder->decode(hex2bin('828684418cf1e3c2e5f23a6ba0ab90f4ff'));
        $decoder->decode(hex2bin('828684be5886a8eb10649cbf'));

        $headers = $decoder->decode(hex2bin('828785bf408825a849e95ba97d7f8925a849e95bb8e8b4bf'));

        static::assertCount(5, $headers);
        static::assertSame(':method', $headers[0]->name);
        static::assertSame('GET', $headers[0]->value);
        static::assertSame(':scheme', $headers[1]->name);
        static::assertSame('https', $headers[1]->value);
        static::assertSame(':path', $headers[2]->name);
        static::assertSame('/index.html', $headers[2]->value);
        static::assertSame(':authority', $headers[3]->name);
        static::assertSame('www.example.com', $headers[3]->value);
        static::assertSame('custom-key', $headers[4]->name);
        static::assertSame('custom-value', $headers[4]->value);
    }

    public function testRfcC5Response1WithoutHuffman(): void
    {
        $decoder = new Decoder(256);

        $headers = $decoder->decode(hex2bin(
            '4803333032580770726976617465611d'
            . '4d6f6e2c203231204f637420323031'
            . '332032303a31333a323120474d546e17'
            . '68747470733a2f2f7777772e657861'
            . '6d706c652e636f6d',
        ));

        static::assertCount(4, $headers);
        static::assertSame(':status', $headers[0]->name);
        static::assertSame('302', $headers[0]->value);
        static::assertSame('cache-control', $headers[1]->name);
        static::assertSame('private', $headers[1]->value);
        static::assertSame('date', $headers[2]->name);
        static::assertSame('Mon, 21 Oct 2013 20:13:21 GMT', $headers[2]->value);
        static::assertSame('location', $headers[3]->name);
        static::assertSame('https://www.example.com', $headers[3]->value);
    }

    public function testRfcC5Response2WithoutHuffman(): void
    {
        $decoder = new Decoder(256);

        $decoder->decode(hex2bin(
            '4803333032580770726976617465611d'
            . '4d6f6e2c203231204f637420323031'
            . '332032303a31333a323120474d546e17'
            . '68747470733a2f2f7777772e657861'
            . '6d706c652e636f6d',
        ));

        $headers = $decoder->decode(hex2bin('4803333037c1c0bf'));

        static::assertCount(4, $headers);
        static::assertSame(':status', $headers[0]->name);
        static::assertSame('307', $headers[0]->value);
        static::assertSame('cache-control', $headers[1]->name);
        static::assertSame('private', $headers[1]->value);
        static::assertSame('date', $headers[2]->name);
        static::assertSame('Mon, 21 Oct 2013 20:13:21 GMT', $headers[2]->value);
        static::assertSame('location', $headers[3]->name);
        static::assertSame('https://www.example.com', $headers[3]->value);
    }

    public function testRfcC5Response3WithoutHuffman(): void
    {
        $decoder = new Decoder(256);

        $decoder->decode(hex2bin(
            '4803333032580770726976617465611d'
            . '4d6f6e2c203231204f637420323031'
            . '332032303a31333a323120474d546e17'
            . '68747470733a2f2f7777772e657861'
            . '6d706c652e636f6d',
        ));

        $decoder->decode(hex2bin('4803333037c1c0bf'));

        $headers = $decoder->decode(hex2bin(
            '88c1611d4d6f6e2c203231204f6374'
            . '20323031332032303a31333a323220'
            . '474d54c05a04677a69707738666f6f'
            . '3d4153444a4b48514b425a584f5157'
            . '454f50495541585157454f49553b20'
            . '6d61782d6167653d333630303b2076'
            . '657273696f6e3d31',
        ));

        static::assertCount(6, $headers);
        static::assertSame(':status', $headers[0]->name);
        static::assertSame('200', $headers[0]->value);
        static::assertSame('cache-control', $headers[1]->name);
        static::assertSame('private', $headers[1]->value);
        static::assertSame('date', $headers[2]->name);
        static::assertSame('Mon, 21 Oct 2013 20:13:22 GMT', $headers[2]->value);
        static::assertSame('location', $headers[3]->name);
        static::assertSame('https://www.example.com', $headers[3]->value);
        static::assertSame('content-encoding', $headers[4]->name);
        static::assertSame('gzip', $headers[4]->value);
        static::assertSame('set-cookie', $headers[5]->name);
        static::assertSame('foo=ASDJKHQKBZXOQWEOPIUAXQWEOIU; max-age=3600; version=1', $headers[5]->value);
    }

    public function testRfcC6Response1WithHuffman(): void
    {
        $decoder = new Decoder(256);

        $headers = $decoder->decode(hex2bin(
            '488264025885aec3771a4b6196d07abe'
            . '941054d444a8200595040b8166e082a6'
            . '2d1bff6e919d29ad171863c78f0b97c8'
            . 'e9ae82ae43d3',
        ));

        static::assertCount(4, $headers);
        static::assertSame(':status', $headers[0]->name);
        static::assertSame('302', $headers[0]->value);
        static::assertSame('cache-control', $headers[1]->name);
        static::assertSame('private', $headers[1]->value);
        static::assertSame('date', $headers[2]->name);
        static::assertSame('Mon, 21 Oct 2013 20:13:21 GMT', $headers[2]->value);
        static::assertSame('location', $headers[3]->name);
        static::assertSame('https://www.example.com', $headers[3]->value);
    }

    public function testRfcC6Response2WithHuffman(): void
    {
        $decoder = new Decoder(256);

        $decoder->decode(hex2bin(
            '488264025885aec3771a4b6196d07abe'
            . '941054d444a8200595040b8166e082a6'
            . '2d1bff6e919d29ad171863c78f0b97c8'
            . 'e9ae82ae43d3',
        ));

        $headers = $decoder->decode(hex2bin('4883640effc1c0bf'));

        static::assertCount(4, $headers);
        static::assertSame(':status', $headers[0]->name);
        static::assertSame('307', $headers[0]->value);
    }

    public function testRfcC6Response3WithHuffman(): void
    {
        $decoder = new Decoder(256);

        $decoder->decode(hex2bin(
            '488264025885aec3771a4b6196d07abe'
            . '941054d444a8200595040b8166e082a6'
            . '2d1bff6e919d29ad171863c78f0b97c8'
            . 'e9ae82ae43d3',
        ));

        $decoder->decode(hex2bin('4883640effc1c0bf'));

        $headers = $decoder->decode(hex2bin(
            '88c16196d07abe941054d444a8200595'
            . '040b8166e084a62d1bffc05a839bd9ab'
            . '77ad94e7821dd7f2e6c7b335dfdfcd5b'
            . '3960d5af27087f3672c1ab270fb5291f'
            . '9587316065c003ed4ee5b1063d5007',
        ));

        static::assertCount(6, $headers);
        static::assertSame(':status', $headers[0]->name);
        static::assertSame('200', $headers[0]->value);
        static::assertSame('cache-control', $headers[1]->name);
        static::assertSame('private', $headers[1]->value);
        static::assertSame('date', $headers[2]->name);
        static::assertSame('Mon, 21 Oct 2013 20:13:22 GMT', $headers[2]->value);
        static::assertSame('location', $headers[3]->name);
        static::assertSame('https://www.example.com', $headers[3]->value);
        static::assertSame('content-encoding', $headers[4]->name);
        static::assertSame('gzip', $headers[4]->value);
        static::assertSame('set-cookie', $headers[5]->name);
        static::assertSame('foo=ASDJKHQKBZXOQWEOPIUAXQWEOIU; max-age=3600; version=1', $headers[5]->value);
    }

    public function testErrorIndexZero(): void
    {
        $this->expectException(InvalidTableIndexException::class);

        $decoder = new Decoder();
        $decoder->decode("\x80");
    }

    public function testErrorIndexOutOfRange(): void
    {
        $this->expectException(InvalidTableIndexException::class);

        $decoder = new Decoder();
        $decoder->decode("\xfe");
    }

    public function testErrorTableSizeUpdateMidBlock(): void
    {
        $this->expectException(DecodingException::class);

        $decoder = new Decoder();
        $decoder->decode("\x82\x20");
    }

    public function testErrorHeaderListSizeExceeded(): void
    {
        $this->expectException(HeaderListSizeException::class);

        $decoder = new Decoder(4096, 50);
        $decoder->decode(hex2bin('400a637573746f6d2d6b65790d637573746f6d2d686561646572'));
    }

    public function testErrorTruncatedData(): void
    {
        $this->expectException(DecodingException::class);

        $decoder = new Decoder();
        $decoder->decode(hex2bin('418cf1e3c2'));
    }

    public function testSizeUpdateToZero(): void
    {
        $decoder = new Decoder();

        $decoder->decode(hex2bin('400a637573746f6d2d6b65790d637573746f6d2d686561646572'));

        $headers = $decoder->decode("\x20\x82");

        static::assertCount(1, $headers);
        static::assertSame(':method', $headers[0]->name);
        static::assertSame('GET', $headers[0]->value);
    }

    public function testMultipleConsecutiveSizeUpdates(): void
    {
        $decoder = new Decoder();

        $headers = $decoder->decode("\x20\x3f\xe1\x1f\x82");

        static::assertCount(1, $headers);
        static::assertSame(':method', $headers[0]->name);
        static::assertSame('GET', $headers[0]->value);
    }

    public function testIntegerOverflow(): void
    {
        $this->expectException(IntegerOverflowException::class);

        $decoder = new Decoder();
        $decoder->decode("\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\x01");
    }

    public function testHeaderListSizeExceededMultipleHeaders(): void
    {
        $this->expectException(HeaderListSizeException::class);

        $decoder = new Decoder(4096, 100);

        $decoder->decode(hex2bin('400a637573746f6d2d6b65790d637573746f6d2d686561646572'
        . '400a637573746f6d2d6b65790d637573746f6d2d686561646572'));
    }

    public function testHeaderListSizeExactBoundary(): void
    {
        $decoder = new Decoder(4096, 55);

        $headers = $decoder->decode(hex2bin('400a637573746f6d2d6b65790d637573746f6d2d686561646572'));

        static::assertCount(1, $headers);
        static::assertSame('custom-key', $headers[0]->name);
    }

    public function testHeaderListSizeExactBoundaryPlusOne(): void
    {
        $this->expectException(HeaderListSizeException::class);

        $decoder = new Decoder(4096, 54);
        $decoder->decode(hex2bin('400a637573746f6d2d6b65790d637573746f6d2d686561646572'));
    }

    public function testTableSizeUpdateActuallyApplied(): void
    {
        $decoder = new Decoder(4096);

        $decoder->decode(hex2bin('400a637573746f6d2d6b65790d637573746f6d2d686561646572'));

        $decoder->decode("\x20");

        $this->expectException(InvalidTableIndexException::class);
        $decoder->decode("\xbe");
    }

    public function testResizeActuallyApplied(): void
    {
        $decoder = new Decoder(4096);

        $decoder->decode(hex2bin('400a637573746f6d2d6b65790d637573746f6d2d686561646572'));

        $decoder->resize(0);

        $this->expectException(InvalidTableIndexException::class);
        $decoder->decode("\xbe");
    }

    public function testStaticTableIndex61(): void
    {
        $decoder = new Decoder();

        $headers = $decoder->decode("\xbd");

        static::assertCount(1, $headers);
        static::assertSame('www-authenticate', $headers[0]->name);
        static::assertSame('', $headers[0]->value);
    }

    public function testErrorIndexOutOfRangeMessage(): void
    {
        $decoder = new Decoder();

        try {
            $decoder->decode("\xfe");
            static::fail('Expected InvalidTableIndexException');
        } catch (InvalidTableIndexException $e) {
            static::assertStringContainsString('126', $e->getMessage());
            static::assertStringContainsString('61', $e->getMessage());
        }
    }

    public function testDecodeStringAtExactEnd(): void
    {
        $this->expectException(DecodingException::class);

        $decoder = new Decoder();
        $decoder->decode("\x40\x03foo");
    }

    public function testDecodeStringWithInvalidLength(): void
    {
        $this->expectException(DecodingException::class);

        $decoder = new Decoder();
        $decoder->decode("\x40\x03foo\x0a" . 'short');
    }

    public function testErrorIndexZeroThrows(): void
    {
        try {
            $decoder = new Decoder();
            $decoder->decode("\x80");
            static::fail('Expected InvalidTableIndexException');
        } catch (InvalidTableIndexException $e) {
            static::assertStringContainsString('not valid', $e->getMessage());
        }
    }

    public function testTableSizeUpdateDecodesCorrectValue(): void
    {
        $decoder = new Decoder(4096);

        $decoder->decode(hex2bin('400a637573746f6d2d6b65790d637573746f6d2d686561646572'));

        $decoder->decode("\x3f\x61");

        $headers = $decoder->decode("\xbe");
        static::assertCount(1, $headers);
        static::assertSame('custom-key', $headers[0]->name);
        static::assertSame('custom-header', $headers[0]->value);
    }

    public function testConstructorAcceptsZeroTableSize(): void
    {
        $decoder = new Decoder(0);

        $headers = $decoder->decode("\x82");

        static::assertCount(1, $headers);
        static::assertSame(':method', $headers[0]->name);
    }

    public function testConstructorAcceptsZeroHeaderListSize(): void
    {
        $decoder = new Decoder(4096, 0);

        $headers = $decoder->decode('');

        static::assertSame([], $headers);
    }

    public function testLiteralWithoutIndexingIsNotSensitive(): void
    {
        $decoder = new Decoder();

        $headers = $decoder->decode(hex2bin('000a637573746f6d2d6b65790d637573746f6d2d686561646572'));

        static::assertCount(1, $headers);
        static::assertSame('custom-key', $headers[0]->name);
        static::assertSame('custom-header', $headers[0]->value);
        static::assertFalse($headers[0]->sensitive);
    }

    public function testErrorIndexOutOfRangeWithDynamicEntries(): void
    {
        $decoder = new Decoder();

        $decoder->decode(hex2bin('400a637573746f6d2d6b65790d637573746f6d2d686561646572'));

        try {
            $decoder->decode("\xbf");
            static::fail('Expected InvalidTableIndexException');
        } catch (InvalidTableIndexException $e) {
            static::assertStringContainsString('62', $e->getMessage());
        }
    }
}
