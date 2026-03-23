<?php

declare(strict_types=1);

namespace Psl\DNS\Internal;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\DNS\Exception\ProtocolException;
use Psl\IO;
use Psl\Network;

use function pack;
use function strlen;
use function unpack;

/**
 * Performs the TCP DNS wire exchange per RFC 1035 Section 4.2.2:
 * length-prefixed write followed by length-prefixed read.
 *
 * Send a DNS packet over a TCP stream and read back the response.
 *
 * @internal
 *
 * @throws CancelledException If the operation is cancelled.
 * @throws IO\Exception\RuntimeException If a read/write operation fails.
 * @throws ProtocolException If the response is too short.
 */
function tcp_exchange(Network\StreamInterface $stream, string $packet, CancellationTokenInterface $cancellation): string
{
    $tcpPacket = pack('n', strlen($packet)) . $packet;

    $stream->writeAll($tcpPacket, $cancellation);

    $lengthData = $stream->readFixedSize(2, $cancellation);
    $length = unpack('n', $lengthData)[1];

    if ($length < 12) {
        throw ProtocolException::forTooShort($length);
    }

    return $stream->readFixedSize($length, $cancellation);
}
