<?php

declare(strict_types=1);

namespace Psl\H2\Frame;

use Psl\H2\Exception\FrameDecodingException;

use function ord;
use function strlen;
use function substr;
use function unpack;

/**
 * Decode a binary string into a RawFrame.
 *
 * Reads the 9-byte frame header starting at the given offset, extracts the payload,
 * and returns the decoded RawFrame along with the new offset past the frame.
 *
 * @throws FrameDecodingException If the data is insufficient for the frame header or the declared payload length.
 *
 * @return array{RawFrame, int} The decoded frame and the byte offset immediately after the frame.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-4.1
 */
function decode(string $data, int $offset = 0): array
{
    $available = strlen($data) - $offset;
    if ($available < 9) {
        throw FrameDecodingException::forInsufficientData(9, $available);
    }

    $length = (ord($data[$offset]) << 16) | (ord($data[$offset + 1]) << 8) | ord($data[$offset + 2]);
    $type = ord($data[$offset + 3]);
    $flags = ord($data[$offset + 4]);
    /** @var int<0, max> $streamId */
    $streamId = unpack('N', $data, $offset + 5)[1] & 0x7FFF_FFFF;

    if ($available < (9 + $length)) {
        throw FrameDecodingException::forInsufficientData(9 + $length, $available);
    }

    $payload = $length > 0 ? substr($data, $offset + 9, $length) : '';
    $newOffset = $offset + 9 + $length;

    return [new RawFrame($type, $flags, $streamId, $payload), $newOffset];
}
