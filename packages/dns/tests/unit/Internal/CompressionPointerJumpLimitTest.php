<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\Binary\Writer;
use Psl\DNS\Exception\ProtocolException;
use Psl\DNS\Internal\Decoder;
use Psl\Str\Byte;

final class CompressionPointerJumpLimitTest extends TestCase
{
    public function testAccepts15PointerJumps(): void
    {
        $data = self::buildPointerChainPacket(16);

        $response = Decoder::decode($data);

        static::assertSame(0x1234, $response->id);
    }

    public function testRejectsMoreThan15PointerJumps(): void
    {
        $data = self::buildPointerChainPacket(17);

        $this->expectException(ProtocolException::class);

        Decoder::decode($data);
    }

    private static function buildPointerChainPacket(int $answerCount): string
    {
        $header = new Writer()
            ->u16(0x1234)
            ->u16(0x8180)
            ->u16(0)
            ->u16($answerCount)
            ->u16(0)
            ->u16(0)
            ->toString();

        $data = $header;

        $answerFixed = new Writer()
            ->u16(1)
            ->u16(1)
            ->u32(300)
            ->u16(4)
            ->toString();
        $rdata = Byte\chr(1) . Byte\chr(2) . Byte\chr(3) . Byte\chr(4);

        $nameOffsets = [];
        $nameOffsets[] = Byte\length($data);
        $data .= "\x03foo\x00";
        $data .= $answerFixed . $rdata;

        for ($i = 1; $i < $answerCount; $i++) {
            $nameOffsets[] = Byte\length($data);
            /** @var int<0, max> $offset */
            $offset = $i - 1;
            $prevOffset = $nameOffsets[$offset];
            $data .= Byte\chr(0xC0 | ($prevOffset >> 8)) . Byte\chr($prevOffset & 0xFF);
            $data .= $answerFixed . $rdata;
        }

        return $data;
    }
}
