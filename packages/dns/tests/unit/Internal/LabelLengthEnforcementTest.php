<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\Binary\Writer;
use Psl\DNS\Exception\ProtocolException;
use Psl\DNS\Internal\Decoder;
use Psl\Str;
use Psl\Str\Byte;

final class LabelLengthEnforcementTest extends TestCase
{
    public function testRejectsLabelLongerThan63Bytes(): void
    {
        $longLabel = Str\repeat('a', 64);
        $encodedName = Byte\chr(64) . $longLabel . "\x03com\x00";

        $rdata = "\x01\x02\x03\x04";

        $writer = new Writer();
        $writer = $writer->u16(0x1234)->u16(0x8180)->u16(0)->u16(1)->u16(0)->u16(0);

        $writer = $writer->bytes($encodedName);
        $writer = $writer->u16(1)->u16(1);
        $writer = $writer->u32(300);
        $writer = $writer->u16(Byte\length($rdata));
        $writer = $writer->bytes($rdata);

        $this->expectException(ProtocolException::class);

        Decoder::decode($writer->toString());
    }

    public function testAcceptsLabelExactly63Bytes(): void
    {
        $label63 = Str\repeat('a', 63);
        $encodedName = Byte\chr(63) . $label63 . "\x03com\x00";

        $rdata = "\x01\x02\x03\x04";

        $writer = new Writer();
        $writer = $writer->u16(0x1234)->u16(0x8180)->u16(0)->u16(1)->u16(0)->u16(0);

        $writer = $writer->bytes($encodedName);
        $writer = $writer->u16(1)->u16(1);
        $writer = $writer->u32(300);
        $writer = $writer->u16(Byte\length($rdata));
        $writer = $writer->bytes($rdata);

        $response = Decoder::decode($writer->toString());

        static::assertCount(1, $response->answers);
    }

    public function testRejectsNameLongerThan253Characters(): void
    {
        $labels = [];
        $encoded = '';
        for ($i = 0; $i < 43; $i++) {
            $label = Str\repeat('a', 6);
            $labels[] = $label;
            $encoded .= Byte\chr(6) . $label;
        }

        $encoded .= "\x00";

        $expectedName = Str\join($labels, '.');
        static::assertGreaterThan(253, Byte\length($expectedName));

        $rdata = "\x01\x02\x03\x04";

        $writer = new Writer();
        $writer = $writer->u16(0x1234)->u16(0x8180)->u16(0)->u16(1)->u16(0)->u16(0);

        $writer = $writer->bytes($encoded);
        $writer = $writer->u16(1)->u16(1);
        $writer = $writer->u32(300);
        $writer = $writer->u16(Byte\length($rdata));
        $writer = $writer->bytes($rdata);

        $this->expectException(ProtocolException::class);

        Decoder::decode($writer->toString());
    }

    public function testRejectsReservedLabelLengthByte(): void
    {
        $encodedName = Byte\chr(0x40) . Str\repeat('a', 64) . "\x00";

        $rdata = "\x01\x02\x03\x04";

        $writer = new Writer();
        $writer = $writer->u16(0x1234)->u16(0x8180)->u16(0)->u16(1)->u16(0)->u16(0);

        $writer = $writer->bytes($encodedName);
        $writer = $writer->u16(1)->u16(1);
        $writer = $writer->u32(300);
        $writer = $writer->u16(Byte\length($rdata));
        $writer = $writer->bytes($rdata);

        $this->expectException(ProtocolException::class);

        Decoder::decode($writer->toString());
    }
}
