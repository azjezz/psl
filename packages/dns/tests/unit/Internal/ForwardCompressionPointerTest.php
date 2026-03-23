<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\Binary\Writer;
use Psl\DNS\Exception\ProtocolException;
use Psl\DNS\Internal\Decoder;
use Psl\DNS\Record\RecordType;

final class ForwardCompressionPointerTest extends TestCase
{
    public function testBackwardPointerDecodesSuccessfully(): void
    {
        $header = new Writer()
            ->u16(0x1234)
            ->u16(0x8180)
            ->u16(1)
            ->u16(1)
            ->u16(0)
            ->u16(0)
            ->toString();

        $questionName = "\x07example\x03com\x00";
        $questionFields = new Writer()
            ->u16(RecordType::A->value)
            ->u16(1)
            ->toString();

        $pointerToName = "\xc0\x0c";

        $recordHeader = new Writer()
            ->u16(RecordType::A->value)
            ->u16(1)
            ->u32(300)
            ->u16(4)
            ->toString();

        $rdata = "\x01\x02\x03\x04";

        $packet = $header . $questionName . $questionFields . $pointerToName . $recordHeader . $rdata;

        $response = Decoder::decode($packet);

        static::assertCount(1, $response->answers);
    }

    public function testForwardPointerIsRejected(): void
    {
        $header = new Writer()
            ->u16(0x1234)
            ->u16(0x8180)
            ->u16(1)
            ->u16(0)
            ->u16(0)
            ->u16(0)
            ->toString();

        $forwardPointer = "\x03www\xc0\x20";

        $qtype = new Writer()
            ->u16(RecordType::A->value)
            ->u16(1)
            ->toString();

        $targetName = "\x07example\x03com\x00";

        $packet = $header . $forwardPointer . $qtype . $targetName;

        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('forward compression pointer');

        Decoder::decode($packet);
    }

    public function testSelfReferencingPointerIsRejected(): void
    {
        $header = new Writer()
            ->u16(0x1234)
            ->u16(0x8180)
            ->u16(1)
            ->u16(0)
            ->u16(0)
            ->u16(0)
            ->toString();

        $selfPointer = "\xc0\x0c";

        $packet = $header . $selfPointer;

        $this->expectException(ProtocolException::class);

        Decoder::decode($packet);
    }
}
