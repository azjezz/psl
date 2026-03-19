<?php

declare(strict_types=1);

namespace Psl\HPACK\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\HPACK\Decoder;
use Psl\HPACK\Encoder;
use Psl\HPACK\Exception\DecodingException;
use Psl\HPACK\Exception\HeaderListSizeException;
use Psl\HPACK\Exception\IntegerOverflowException;
use Psl\HPACK\Exception\InvalidSizeException;
use Psl\HPACK\Exception\InvalidTableIndexException;
use Psl\HPACK\Header;
use Psl\HPACK\Internal\Huffman;

use function chr;
use function hex2bin;
use function str_repeat;

final class EdgeCasesTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Huffman::resetDecodeMachine();
    }

    public function testHuffmanExcessivePaddingOneByte(): void
    {
        $this->expectException(DecodingException::class);

        Huffman::decode("\xFF");
    }

    public function testHuffmanExcessivePaddingTwoBytes(): void
    {
        $this->expectException(DecodingException::class);

        Huffman::decode("\xFF\xFF");
    }

    public function testHuffmanEightBitPaddingAfterSymbol(): void
    {
        $this->expectException(DecodingException::class);

        Huffman::decode("\x07\xFF");
    }

    public function testHuffmanSevenBitPaddingIsValid(): void
    {
        $result = Huffman::decode("\x07");

        static::assertSame('0', $result);
    }

    public function testHuffmanValidPaddingAccepted(): void
    {
        $result = Huffman::decode(hex2bin('003f'));

        static::assertSame('00', $result);
    }

    public function testDecoderTableSizeUpdateExceedsMaximum(): void
    {
        $decoder = new Decoder(256);

        $this->expectException(DecodingException::class);

        $decoder->decode("\x3f\xe2\x01");
    }

    public function testDecoderTableSizeUpdateAtMaximum(): void
    {
        $decoder = new Decoder(256);

        $headers = $decoder->decode("\x3f\xe1\x01\x82");

        static::assertCount(1, $headers);
        static::assertSame(':method', $headers[0]->name);
        static::assertSame('GET', $headers[0]->value);
    }

    public function testDecoderTableSizeUpdateBelowMaximum(): void
    {
        $decoder = new Decoder(256);

        $headers = $decoder->decode("\x3f\x61\x82");

        static::assertCount(1, $headers);
        static::assertSame(':method', $headers[0]->name);
    }

    public function testDecoderMassiveTableSizeUpdateRejected(): void
    {
        $decoder = new Decoder(4096);

        $this->expectException(DecodingException::class);

        $decoder->decode("\x3f\xff\xff\xff\x07");
    }

    public function testDecoderRejectsIndexZeroInIndexedField(): void
    {
        $this->expectException(InvalidTableIndexException::class);

        $decoder = new Decoder();
        $decoder->decode("\x80");
    }

    public function testDecoderRejectsIndexZeroInLiteralIncremental(): void
    {
        $this->expectException(InvalidTableIndexException::class);

        $decoder = new Decoder();
        $decoder->decode("\xC0");
    }

    public function testDecoderTruncatedIntegerContinuationGivesInvalidIndex(): void
    {
        $this->expectException(InvalidTableIndexException::class);

        $decoder = new Decoder();
        $decoder->decode("\xbf\x80");
    }

    public function testDecoderRejectsStringLengthExceedingData(): void
    {
        $this->expectException(DecodingException::class);

        $decoder = new Decoder();
        $decoder->decode("\x40\x03ab\x01x");
    }

    public function testDecoderTruncatedBlockAfterNameString(): void
    {
        $this->expectException(DecodingException::class);

        $decoder = new Decoder();
        $decoder->decode("\x40\x01x");
    }

    public function testDecoderHeaderListBombMultipleSmallHeaders(): void
    {
        $this->expectException(HeaderListSizeException::class);

        $decoder = new Decoder(4096, 200);

        $block = '';
        for ($i = 0; $i < 10; $i++) {
            $block .= "\x82";
        }

        $decoder->decode($block);
    }

    public function testDecoderIntegerOverflowInIndex(): void
    {
        $this->expectException(IntegerOverflowException::class);

        $decoder = new Decoder();
        $decoder->decode("\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF\x01");
    }

    public function testDecoderIntegerOverflowInStringLength(): void
    {
        $this->expectException(IntegerOverflowException::class);

        $decoder = new Decoder();
        $decoder->decode("\x40\x01x\x7F\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF\x01");
    }

    public function testDecoderDynamicTableEvictionUnderPressure(): void
    {
        $decoder = new Decoder(79);

        $decoder->decode(hex2bin('4004616161610461616161'));

        $decoder->decode(hex2bin('4004626262620462626262'));

        $headers = $decoder->decode("\xbe");
        static::assertSame('bbbb', $headers[0]->name);

        $this->expectException(InvalidTableIndexException::class);
        $decoder->decode("\xbf");
    }

    public function testDecoderNeverIndexedFieldNotAddedToTable(): void
    {
        $decoder = new Decoder();

        $decoder->decode(hex2bin('100870617373776f726406736563726574'));

        $this->expectException(InvalidTableIndexException::class);
        $decoder->decode("\xbe");
    }

    public function testDecoderLiteralWithoutIndexingNotAddedToTable(): void
    {
        $decoder = new Decoder();

        $decoder->decode(hex2bin('000870617373776f726406736563726574'));

        $this->expectException(InvalidTableIndexException::class);
        $decoder->decode("\xbe");
    }

    public function testDecoderRejectsTableSizeUpdateAfterFirstHeader(): void
    {
        $this->expectException(DecodingException::class);

        $decoder = new Decoder();
        $decoder->decode("\x82\x20");
    }

    public function testDecoderTableSizeOscillation(): void
    {
        $decoder = new Decoder(4096);

        $decoder->decode(hex2bin('400a637573746f6d2d6b65790d637573746f6d2d686561646572'));

        $decoder->decode("\x20");

        $decoder->decode("\x3f\xe1\x1f");

        $this->expectException(InvalidTableIndexException::class);
        $decoder->decode("\xbe");
    }

    public function testDecoderEmptyBlockReturnsEmpty(): void
    {
        $decoder = new Decoder();

        static::assertSame([], $decoder->decode(''));
    }

    public function testDecoderBlockWithOnlyTableSizeUpdate(): void
    {
        $decoder = new Decoder();

        $headers = $decoder->decode("\x20");

        static::assertSame([], $headers);
    }

    public function testHuffmanAllSingleBytesRoundTrip(): void
    {
        for ($i = 0; $i < 256; $i++) {
            $char = chr($i);
            $encoded = Huffman::encode($char);
            $decoded = Huffman::decode($encoded);
            static::assertSame($char, $decoded, 'Failed for byte ' . $i);
        }
    }

    public function testHuffmanNullByteString(): void
    {
        $input = "\x00\x00\x00";
        $encoded = Huffman::encode($input);
        $decoded = Huffman::decode($encoded);

        static::assertSame($input, $decoded);
    }

    public function testHuffmanBinaryData(): void
    {
        $input = '';
        for ($i = 0; $i < 256; $i++) {
            $input .= chr($i);
        }

        $encoded = Huffman::encode($input);
        $decoded = Huffman::decode($encoded);

        static::assertSame($input, $decoded);
    }

    public function testEncoderDecoderBinaryHeaderValues(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder(4096, 1_000_000);

        $binaryValue = '';
        for ($i = 0; $i < 256; $i++) {
            $binaryValue .= chr($i);
        }

        $headers = [new Header('x-binary', $binaryValue)];
        $encoded = $encoder->encode($headers);
        $decoded = $decoder->decode($encoded);

        static::assertCount(1, $decoded);
        static::assertSame($binaryValue, $decoded[0]->value);
    }

    public function testEncoderDecoderEmptyValue(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $headers = [new Header('x', '')];
        $encoded = $encoder->encode($headers);
        $decoded = $decoder->decode($encoded);

        static::assertCount(1, $decoded);
        static::assertSame('x', $decoded[0]->name);
        static::assertSame('', $decoded[0]->value);
    }

    public function testDynamicTableNegativeIndexReturnsNull(): void
    {
        $decoder = new Decoder();

        $this->expectException(InvalidTableIndexException::class);
        $decoder->decode("\xbe");
    }

    public function testDecoderRejectsHuffmanWithEosSymbol(): void
    {
        $this->expectException(DecodingException::class);

        Huffman::decode("\xFF\xFF\xFF\xFC");
    }

    public function testDecoderSensitiveFieldPreservedAcrossBlocks(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $e1 = $encoder->encode([new Header('x-secret', 'value1', true)]);
        $decoded1 = $decoder->decode($e1);
        static::assertTrue($decoded1[0]->sensitive);

        $e2 = $encoder->encode([new Header('x-public', 'value2')]);
        $decoded2 = $decoder->decode($e2);
        static::assertFalse($decoded2[0]->sensitive);
    }

    public function testDecoderMaxStaticIndex(): void
    {
        $decoder = new Decoder();

        $headers = $decoder->decode("\xbd");

        static::assertCount(1, $headers);
        static::assertSame('www-authenticate', $headers[0]->name);
        static::assertSame('', $headers[0]->value);
    }

    public function testDecoderStaticIndexBoundary62(): void
    {
        $this->expectException(InvalidTableIndexException::class);

        $decoder = new Decoder();
        $decoder->decode("\xbe");
    }

    public function testMultipleResizesEmitsBothUpdates(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $encoder->resize(128);
        $encoder->resize(256);

        $encoded = $encoder->encode([new Header(':method', 'GET')]);
        $decoder->resize(256);
        $decoded = $decoder->decode($encoded);

        static::assertCount(1, $decoded);
        static::assertSame('GET', $decoded[0]->value);
    }

    public function testMultipleResizesMinThenFinal(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $encoder->resize(0);
        $encoder->resize(4096);

        $encoded = $encoder->encode([new Header(':method', 'GET')]);
        $decoded = $decoder->decode($encoded);

        static::assertCount(1, $decoded);
        static::assertSame(':method', $decoded[0]->name);
        static::assertSame('GET', $decoded[0]->value);
    }

    public function testMultipleResizesSameValueEmitsOne(): void
    {
        $encoder = new Encoder();
        $decoder = new Decoder();

        $encoder->resize(256);
        $encoder->resize(256);

        $encoded = $encoder->encode([new Header(':method', 'GET')]);
        $decoder->resize(256);
        $decoded = $decoder->decode($encoded);

        static::assertCount(1, $decoded);
        static::assertSame('GET', $decoded[0]->value);
    }

    public function testEncoderHeaderListSizeExceeded(): void
    {
        $this->expectException(HeaderListSizeException::class);

        $encoder = new Encoder(4096, 100);

        $encoder->encode([
            new Header('x-large', str_repeat('x', 100)),
        ]);
    }

    public function testEncoderHeaderListSizeAccumulates(): void
    {
        $this->expectException(HeaderListSizeException::class);

        $encoder = new Encoder(4096, 200);

        $encoder->encode([
            new Header('x-a', str_repeat('a', 80)),
            new Header('x-b', str_repeat('b', 80)),
        ]);
    }

    public function testEncoderNegativeTableSizeThrows(): void
    {
        $this->expectException(InvalidSizeException::class);

        // @mago-expect analysis:invalid-argument - testing runtime validation
        new Encoder(-1);
    }

    public function testEncoderNegativeHeaderListSizeThrows(): void
    {
        $this->expectException(InvalidSizeException::class);

        // @mago-expect analysis:invalid-argument - testing runtime validation
        new Encoder(4096, -1);
    }

    public function testEncoderResizeNegativeThrows(): void
    {
        $this->expectException(InvalidSizeException::class);

        $encoder = new Encoder();
        // @mago-expect analysis:invalid-argument - testing runtime validation
        $encoder->resize(-1);
    }

    public function testDecoderNegativeTableSizeThrows(): void
    {
        $this->expectException(InvalidSizeException::class);

        // @mago-expect analysis:invalid-argument - testing runtime validation
        new Decoder(-1);
    }

    public function testDecoderNegativeHeaderListSizeThrows(): void
    {
        $this->expectException(InvalidSizeException::class);

        // @mago-expect analysis:invalid-argument - testing runtime validation
        new Decoder(4096, -1);
    }

    public function testDecoderResizeNegativeThrows(): void
    {
        $this->expectException(InvalidSizeException::class);

        $decoder = new Decoder();
        // @mago-expect analysis:invalid-argument - testing runtime validation
        $decoder->resize(-1);
    }

    public function testDecoderRejectsThreeTableSizeUpdates(): void
    {
        $this->expectException(DecodingException::class);

        $decoder = new Decoder(4096);
        $decoder->decode("\x20\x20\x20");
    }

    public function testDecoderAcceptsTwoTableSizeUpdates(): void
    {
        $decoder = new Decoder(4096);

        $headers = $decoder->decode("\x20\x3f\xe1\x1f\x82");

        static::assertCount(1, $headers);
        static::assertSame(':method', $headers[0]->name);
    }

    public function testStaticFullMatchPreferredOverDynamic(): void
    {
        $encoder = new Encoder();

        $encoder->encode([new Header(':method', 'GET')]);

        $encoded = $encoder->encode([new Header(':method', 'GET')]);

        static::assertSame("\x82", $encoded);
    }
}
