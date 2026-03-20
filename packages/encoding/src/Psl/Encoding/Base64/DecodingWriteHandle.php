<?php

declare(strict_types=1);

namespace Psl\Encoding\Base64;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\Encoding\Exception;
use Psl\IO;

use function preg_replace;
use function strlen;
use function substr;

/**
 * A write handle that accepts base64-encoded bytes, strips whitespace,
 * buffers until 4-byte groups are available, decodes and writes to the inner handle.
 */
final class DecodingWriteHandle implements IO\WriteHandleInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;

    private string $remainder = '';

    public function __construct(
        private readonly IO\WriteHandleInterface $handle,
        private readonly Variant $variant = Variant::Standard,
        private readonly bool $padding = true,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function tryWrite(string $bytes): int
    {
        $length = strlen($bytes);

        /** @var string $bytes */
        $bytes = preg_replace('/\s+/', '', $bytes);
        $data = $this->remainder . $bytes;

        $dataLength = strlen($data);
        $usable = $dataLength - ($dataLength % 4);

        if ($usable > 0) {
            $decoded = namespace\decode(substr($data, 0, $usable), $this->variant, $this->padding);
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
     * Flush any remaining buffered base64 data through the decoder to the inner handle.
     *
     * @throws Exception\RangeException If the remaining bytes do not form valid base64.
     */
    public function flush(): void
    {
        if ($this->remainder !== '') {
            $decoded = namespace\decode($this->remainder, $this->variant, $this->padding);
            $this->remainder = '';
            $this->handle->writeAll($decoded);
        }
    }
}
