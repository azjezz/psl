<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\DNS\Exception\ProtocolException;
use Psl\DNS\Internal;
use Psl\Network;
use Psl\Str;
use Psl\Str\Byte;

final class TcpExchangeTest extends TestCase
{
    public function testRejectsZeroLengthResponse(): void
    {
        $stream = $this->createMockStream("\x00\x00");

        $cancellation = new Async\TimeoutCancellationToken(Duration::seconds(5));

        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('DNS response too short');

        Internal\tcp_exchange($stream, 'test', $cancellation);
    }

    public function testRejectsResponseShorterThan12Bytes(): void
    {
        $stream = $this->createMockStream("\x00\x05hello");

        $cancellation = new Async\TimeoutCancellationToken(Duration::seconds(5));

        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('DNS response too short');

        Internal\tcp_exchange($stream, 'test', $cancellation);
    }

    public function testAcceptsMinimalValidResponse(): void
    {
        $payload = Str\repeat("\x00", 12);
        $stream = $this->createMockStream("\x00\x0c" . $payload);

        $cancellation = new Async\TimeoutCancellationToken(Duration::seconds(5));

        $result = Internal\tcp_exchange($stream, 'test', $cancellation);

        static::assertSame(12, Byte\length($result));
    }

    private function createMockStream(string $readBuffer): Network\StreamInterface
    {
        $buffer = $readBuffer;

        $stream = $this->createStub(Network\StreamInterface::class);

        $stream
            ->method('readFixedSize')
            ->willReturnCallback(static function (int $size) use (&$buffer): string {
                /** @var non-negative-int $size */
                $result = Byte\slice($buffer, 0, $size);
                $buffer = Byte\slice($buffer, $size);
                return $result;
            });

        return $stream;
    }
}
