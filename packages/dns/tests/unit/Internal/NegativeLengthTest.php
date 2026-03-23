<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\Binary\Writer;
use Psl\DNS\Exception\ProtocolException;
use Psl\DNS\Internal\Decoder;
use Psl\Iter;
use Psl\Str;
use Psl\Str\Byte;

final class NegativeLengthTest extends TestCase
{
    public function testRrsigWithRdlengthSmallerThanFixedFieldsThrows(): void
    {
        $rrsigRdata = new Writer();
        $rrsigRdata = $rrsigRdata->u16(1)->u8(8)->u8(2)->u32(300)->u32(0)->u32(0)->u16(12_345);
        $rrsigRdata = $rrsigRdata->bytes($this->encodeName('example.com'));
        $rrsigRdata = $rrsigRdata->bytes('signature-data');
        $fullRdata = $rrsigRdata->toString();

        $packet = $this->buildResponsePacket(
            0x1234,
            0,
            [
                ['example.com', 46, 1],
            ],
            [
                ['name' => 'example.com', 'type' => 46, 'ttl' => 300, 'rdlength' => 10, 'rdata' => $fullRdata],
            ],
        );

        $this->expectException(ProtocolException::class);

        Decoder::decode($packet);
    }

    public function testNsecWithRdlengthSmallerThanNameLengthThrows(): void
    {
        $nsecRdata = $this->encodeName('next.example.com') . "\x00\x01\x40";
        $actualLength = Byte\length($nsecRdata);

        $packet = $this->buildResponsePacket(
            0x1234,
            0,
            [
                ['example.com', 47, 1],
            ],
            [
                ['name' => 'example.com', 'type' => 47, 'ttl' => 300, 'rdlength' => 2, 'rdata' => $nsecRdata],
            ],
        );

        $this->expectException(ProtocolException::class);

        Decoder::decode($packet);
    }

    public function testNsec3WithRdlengthSmallerThanFixedFieldsThrows(): void
    {
        $nsec3Rdata = new Writer();
        $nsec3Rdata = $nsec3Rdata
            ->u8(1)
            ->u8(0)
            ->u16(0)
            ->u8(0)
            ->u8(20)
            ->bytes(Str\repeat("\xAA", 20))
            ->bytes("\x00\x01\x40");
        $fullRdata = $nsec3Rdata->toString();

        $packet = $this->buildResponsePacket(
            0x1234,
            0,
            [
                ['example.com', 50, 1],
            ],
            [
                ['name' => 'example.com', 'type' => 50, 'ttl' => 300, 'rdlength' => 3, 'rdata' => $fullRdata],
            ],
        );

        $this->expectException(ProtocolException::class);

        Decoder::decode($packet);
    }

    /**
     * @param list<array{string, int, int}> $questions
     * @param list<array{name: string, type: int, ttl: int, rdlength: int, rdata: string}> $answers
     */
    private function buildResponsePacket(int $id, int $rcode, array $questions, array $answers): string
    {
        $writer = new Writer();

        $flags = 0x8180 | ($rcode & 0x0F);
        $writer = $writer
            ->u16($id)
            ->u16($flags)
            ->u16(Iter\count($questions))
            ->u16(Iter\count($answers))
            ->u16(0)
            ->u16(0);

        foreach ($questions as [$name, $qtype, $qclass]) {
            $writer = $writer->bytes($this->encodeName($name));
            $writer = $writer->u16($qtype)->u16($qclass);
        }

        foreach ($answers as $answer) {
            $writer = $writer->bytes($this->encodeName($answer['name']));
            $writer = $writer->u16($answer['type'])->u16(1);
            $writer = $writer->u32($answer['ttl']);
            $writer = $writer->u16($answer['rdlength']);
            $writer = $writer->bytes($answer['rdata']);
        }

        return $writer->toString();
    }

    private function encodeName(string $name): string
    {
        $parts = Byte\split($name, '.');
        $encoded = '';
        foreach ($parts as $part) {
            $encoded .= Byte\chr(Byte\length($part)) . $part;
        }

        return $encoded . "\x00";
    }
}
