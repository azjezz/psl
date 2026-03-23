<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Edns;

use PHPUnit\Framework\TestCase;
use Psl\Binary\Writer;
use Psl\DNS\EDNS\KeyTagOption;
use Psl\DNS\Internal\EDNS\EDNSCodec;

final class KeyTagOptionTest extends TestCase
{
    public function testCode(): void
    {
        $option = new KeyTagOption([]);

        static::assertSame(14, $option->code);
    }

    public function testEmptyTags(): void
    {
        $option = new KeyTagOption([]);

        static::assertSame([], $option->tags);
    }

    public function testWithTags(): void
    {
        $option = new KeyTagOption([20_326, 19_036]);

        static::assertSame([20_326, 19_036], $option->tags);
    }

    public function testToWireFormatEmpty(): void
    {
        $option = new KeyTagOption([]);

        static::assertSame('', $option->toWireFormat());
    }

    public function testToWireFormatSingleTag(): void
    {
        $option = new KeyTagOption([20_326]);

        $expected = new Writer()->u16(20_326)->toString();

        static::assertSame($expected, $option->toWireFormat());
    }

    public function testToWireFormatMultipleTags(): void
    {
        $option = new KeyTagOption([20_326, 19_036, 12_345]);

        $expected = new Writer()
            ->u16(20_326)
            ->u16(19_036)
            ->u16(12_345)
            ->toString();

        static::assertSame($expected, $option->toWireFormat());
    }

    public function testRoundTripEmpty(): void
    {
        $original = new KeyTagOption([]);
        $wire = EDNSCodec::encodeOptions([$original]);
        $decoded = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $decoded);
        $keyTag = $decoded[0];
        static::assertInstanceOf(KeyTagOption::class, $keyTag);
        static::assertSame([], $keyTag->tags);
    }

    public function testRoundTripMultipleTags(): void
    {
        $original = new KeyTagOption([20_326, 19_036]);
        $wire = EDNSCodec::encodeOptions([$original]);
        $decoded = EDNSCodec::decodeOptions($wire);

        static::assertCount(1, $decoded);
        $keyTag = $decoded[0];
        static::assertInstanceOf(KeyTagOption::class, $keyTag);
        static::assertSame([20_326, 19_036], $keyTag->tags);
    }
}
