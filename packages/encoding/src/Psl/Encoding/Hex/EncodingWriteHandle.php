<?php

declare(strict_types=1);

namespace Psl\Encoding\Hex;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;

use function strlen;

/**
 * A write handle that accepts raw binary data, hex-encodes it, and writes to the inner handle.
 *
 * No buffering is needed since any chunk of binary data can be independently hex-encoded.
 */
final class EncodingWriteHandle implements IO\WriteHandleInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;

    public function __construct(
        private readonly IO\WriteHandleInterface $handle,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function tryWrite(string $bytes): int
    {
        $length = strlen($bytes);

        if ($length > 0) {
            $encoded = namespace\encode($bytes);
            $this->handle->writeAll($encoded);
        }

        return $length;
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $bytes, CancellationTokenInterface $cancellation = new NullCancellationToken()): int
    {
        return $this->tryWrite($bytes);
    }
}
