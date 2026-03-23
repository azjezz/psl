<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\Binary\Writer;
use Psl\DNS\Internal\Decoder;
use Psl\DNS\Record\ARecord;
use Psl\Iter;
use Psl\Str;
use Psl\Str\Byte;
use Throwable;

final class RdlengthValidationTest extends TestCase
{
    public function testDecoderSkipsExcessRdataWhenRdlengthExceedsConsumedBytes(): void
    {
        $rdata = "\x01\x02\x03\x04" . Str\repeat("\x00", 96);

        $packet = $this->buildResponsePacket(
            0x1234,
            0,
            [
                ['example.com', 1, 1],
            ],
            [
                ['name' => 'example.com', 'type' => 1, 'ttl' => 300, 'rdata' => $rdata],
                ['name' => 'example.com', 'type' => 1, 'ttl' => 300, 'rdata' => "\x05\x06\x07\x08"],
            ],
        );

        $response = Decoder::decode($packet);

        static::assertCount(2, $response->answers);
        static::assertInstanceOf(ARecord::class, $response->answers[0]);
        static::assertInstanceOf(ARecord::class, $response->answers[1]);
        static::assertSame('1.2.3.4', $response->answers[0]->address->toString());
        static::assertSame('5.6.7.8', $response->answers[1]->address->toString());
    }

    public function testDecoderRejectsRdlengthShorterThanConsumedBytes(): void
    {
        $writer = new Writer();
        $writer = $writer->u16(0x1234)->u16(0x8180)->u16(1)->u16(1)->u16(0)->u16(0);

        $writer = $writer->bytes($this->encodeName('example.com'));
        $writer = $writer->u16(1)->u16(1);

        $writer = $writer->bytes($this->encodeName('example.com'));
        $writer = $writer->u16(1)->u16(1);
        $writer = $writer->u32(300);
        $writer = $writer->u16(2);
        $writer = $writer->bytes("\x01\x02\x03\x04");

        $packet = $writer->toString();

        $this->expectException(Throwable::class);

        Decoder::decode($packet);
    }

    /**
     * @param list<array{string, int, int}> $questions
     * @param list<array{name: string, type: int, ttl: int, rdata: string}> $answers
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
            $writer = $writer->u16(Byte\length($answer['rdata']));
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
