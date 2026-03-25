<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal\H1;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;

use function min;
use function strlen;

/**
 * Reads exactly Content-Length bytes from an HTTP/1.x response body (RFC 9112 Section 6.2).
 *
 * Tracks the number of remaining bytes and stops reading once the declared
 * content length is consumed. If the underlying stream reaches EOF before all
 * bytes are read, the handle reports EOF early.
 *
 * Read contract:
 * - {@see read()} returns body data up to the remaining byte count.
 * - After all bytes are consumed or the stream reaches EOF, {@see read()}
 *   returns '' and {@see reachedEndOfDataSource()} returns true.
 * - No trailers are available for Content-Length framed responses.
 *
 * @internal
 *
 * @see ResponseReader::buildBody() Creates this handle for Content-Length responses.
 */
final class FixedLengthBodyHandle implements IO\ReadHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;

    /**
     * @var non-negative-int
     */
    private int $remaining;

    /**
     * @param IO\Reader $reader Buffered reader wrapping the connection stream.
     * @param non-negative-int $contentLength The declared Content-Length of the response body.
     */
    public function __construct(
        private readonly IO\Reader $reader,
        int $contentLength,
    ) {
        $this->remaining = $contentLength;
    }

    private bool $eof = false;

    /**
     * Return available data without blocking, limited to the remaining byte count.
     *
     * @inheritDoc
     */
    public function tryRead(null|int $maxBytes = null): string
    {
        if ($this->remaining === 0 || $this->eof) {
            return '';
        }

        $toRead = $maxBytes !== null ? min($maxBytes, $this->remaining) : $this->remaining;
        $data = $this->reader->tryRead($toRead);

        if ($data === '' && $this->reader->reachedEndOfDataSource()) {
            $this->eof = true;
            return '';
        }

        /** @var non-negative-int $remaining */
        $remaining = $this->remaining - strlen($data);
        $this->remaining = $remaining;

        return $data;
    }

    /**
     * Read body data from the wire, limited to the remaining byte count.
     *
     * @inheritDoc
     */
    public function read(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        if ($this->remaining === 0 || $this->eof) {
            return '';
        }

        $toRead = $maxBytes !== null ? min($maxBytes, $this->remaining) : $this->remaining;
        $data = $this->reader->read($toRead, $cancellation);

        if ($data === '' && $this->reader->reachedEndOfDataSource()) {
            $this->eof = true;
            return '';
        }

        /** @var non-negative-int $remaining */
        $remaining = $this->remaining - strlen($data);
        $this->remaining = $remaining;

        return $data;
    }

    /**
     * Whether all Content-Length bytes have been consumed or the stream reached EOF early.
     */
    public function reachedEndOfDataSource(): bool
    {
        return $this->remaining === 0 || $this->eof;
    }
}
