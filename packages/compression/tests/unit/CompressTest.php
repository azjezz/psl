<?php

declare(strict_types=1);

namespace Psl\Compression\Tests\Unit;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use Psl\Compression;
use Psl\Compression\Tests\Fixture\BrotliCompressor;
use Psl\Compression\Tests\Fixture\BrotliDecompressor;

use function brotli_uncompress;
use function str_repeat;
use function strlen;

#[RequiresPhpExtension('brotli')]
final class CompressTest extends TestCase
{
    public function testCompress(): void
    {
        $original = 'hello world';

        $compressed = Compression\compress($original, new BrotliCompressor());

        static::assertNotSame($original, $compressed);
        static::assertSame($original, brotli_uncompress($compressed));
    }

    public function testCompressEmptyString(): void
    {
        $compressed = Compression\compress('', new BrotliCompressor());

        static::assertSame('', brotli_uncompress($compressed));
    }

    public function testCompressLargeData(): void
    {
        $original = str_repeat('The quick brown fox jumps over the lazy dog. ', 1000);

        $compressed = Compression\compress($original, new BrotliCompressor());

        static::assertLessThan(strlen($original), strlen($compressed));
        static::assertSame($original, brotli_uncompress($compressed));
    }

    public function testRoundtrip(): void
    {
        $original = 'roundtrip test data';

        $compressed = Compression\compress($original, new BrotliCompressor());
        $decompressed = Compression\decompress($compressed, new BrotliDecompressor());

        static::assertSame($original, $decompressed);
    }

    public function testRoundtripEmptyString(): void
    {
        $compressed = Compression\compress('', new BrotliCompressor());
        $decompressed = Compression\decompress($compressed, new BrotliDecompressor());

        static::assertSame('', $decompressed);
    }

    public function testRoundtripBinaryData(): void
    {
        $original = "\x00\x01\x02\xff\xfe\xfd";

        $compressed = Compression\compress($original, new BrotliCompressor());
        $decompressed = Compression\decompress($compressed, new BrotliDecompressor());

        static::assertSame($original, $decompressed);
    }

    public function testRoundtripUnicodeData(): void
    {
        $original = 'こんにちは世界 🌍';

        $compressed = Compression\compress($original, new BrotliCompressor());
        $decompressed = Compression\decompress($compressed, new BrotliDecompressor());

        static::assertSame($original, $decompressed);
    }

    public function testCompressorReusableAfterFinish(): void
    {
        $compressor = new BrotliCompressor();

        $first = Compression\compress('first stream', $compressor);
        $second = Compression\compress('second stream', $compressor);
        $third = Compression\compress('third stream', $compressor);

        static::assertSame('first stream', brotli_uncompress($first));
        static::assertSame('second stream', brotli_uncompress($second));
        static::assertSame('third stream', brotli_uncompress($third));
    }

    public function testDecompressorReusableAfterFinish(): void
    {
        $compressor = new BrotliCompressor();
        $decompressor = new BrotliDecompressor();

        $first = Compression\compress('first stream', $compressor);
        $second = Compression\compress('second stream', $compressor);
        $third = Compression\compress('third stream', $compressor);

        static::assertSame('first stream', Compression\decompress($first, $decompressor));
        static::assertSame('second stream', Compression\decompress($second, $decompressor));
        static::assertSame('third stream', Compression\decompress($third, $decompressor));
    }
}
