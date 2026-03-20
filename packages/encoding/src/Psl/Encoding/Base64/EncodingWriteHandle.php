<?php

declare(strict_types=1);

namespace Psl\Encoding\Base64;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;

use function strlen;
use function substr;

/**
 * A write handle that accepts raw binary data, buffers until {@see CHUNK_SIZE} (57) byte chunks
 * are available, base64-encodes each chunk, and writes to the inner handle with {@see LINE_ENDING}.
 */
final class EncodingWriteHandle implements IO\WriteHandleInterface
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
        $data = $this->remainder . $bytes;

        $dataLength = strlen($data);
        while ($dataLength >= namespace\CHUNK_SIZE) {
            $chunk = substr($data, 0, namespace\CHUNK_SIZE);
            $data = substr($data, namespace\CHUNK_SIZE);
            $dataLength -= namespace\CHUNK_SIZE;

            $encoded = namespace\encode($chunk, $this->variant, $this->padding) . namespace\LINE_ENDING;
            $this->handle->writeAll($encoded);
        }

        $this->remainder = $data;
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
     * Flush any remaining buffered raw data through the encoder to the inner handle.
     */
    public function flush(): void
    {
        if ($this->remainder !== '') {
            $encoded = namespace\encode($this->remainder, $this->variant, $this->padding) . namespace\LINE_ENDING;
            $this->remainder = '';
            $this->handle->writeAll($encoded);
        }
    }
}
