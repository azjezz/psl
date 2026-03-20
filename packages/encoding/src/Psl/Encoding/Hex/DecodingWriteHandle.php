<?php

declare(strict_types=1);

namespace Psl\Encoding\Hex;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\Encoding\Exception;
use Psl\IO;

use function strlen;
use function substr;

/**
 * A write handle that accepts hex-encoded bytes, buffers until complete 2-byte pairs
 * are available, decodes and writes to the inner handle.
 */
final class DecodingWriteHandle implements IO\WriteHandleInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;

    private string $remainder = '';

    public function __construct(
        private readonly IO\WriteHandleInterface $handle,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function tryWrite(string $bytes): int
    {
        $length = strlen($bytes);

        $data = $this->remainder . $bytes;

        $dataLength = strlen($data);
        $usable = $dataLength - ($dataLength % 2);

        if ($usable > 0) {
            $decoded = namespace\decode(substr($data, 0, $usable));
            $this->handle->writeAll($decoded);
            $this->remainder = substr($data, $usable);
        } else {
            $this->remainder = $data;
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

    /**
     * Flush any remaining buffered hex data through the decoder to the inner handle.
     *
     * @throws Exception\RangeException If there is an odd number of hex characters remaining.
     */
    public function flush(): void
    {
        if ($this->remainder !== '') {
            // @codeCoverageIgnoreStart
            $decoded = namespace\decode($this->remainder);
            $this->remainder = '';
            $this->handle->writeAll($decoded);
            // @codeCoverageIgnoreEnd
        }
    }
}
