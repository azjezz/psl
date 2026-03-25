<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal\H1;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;

/**
 * Reads the response body until the connection is closed (RFC 9112 Section 7.2).
 *
 * Used as the fallback framing strategy when neither Content-Length nor
 * Transfer-Encoding: chunked is present. The body ends when the underlying
 * stream reaches EOF or an I/O error occurs during reading. I/O errors are
 * treated as EOF since the server may close the connection at any time.
 *
 * Connections using this framing cannot be reused (no keep-alive), because
 * there is no way to determine the body boundary without closing the stream.
 *
 * Read contract:
 * - {@see read()} returns data until the connection closes.
 * - After EOF, {@see read()} returns '' and {@see reachedEndOfDataSource()} returns true.
 * - I/O errors are silently swallowed and treated as EOF.
 *
 * @internal
 *
 * @see ResponseReader::buildBody() Creates this handle as a fallback.
 */
final class UntilCloseBodyHandle implements IO\ReadHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;

    private bool $eof = false;

    /**
     * @param IO\Reader $reader Buffered reader wrapping the connection stream.
     */
    public function __construct(
        private readonly IO\Reader $reader,
    ) {}

    /**
     * @inheritDoc
     */
    public function tryRead(null|int $maxBytes = null): string
    {
        if ($this->eof) {
            return '';
        }

        $data = $this->reader->tryRead($maxBytes);
        if ($data === '' && $this->reader->reachedEndOfDataSource()) {
            $this->eof = true;
        }

        return $data;
    }

    /**
     * Read body data from the wire until the connection closes.
     *
     * I/O errors are caught and treated as EOF, since the server may
     * legitimately close the connection to signal the end of the body.
     *
     * @inheritDoc
     */
    public function read(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        if ($this->eof) {
            return '';
        }

        try {
            $data = $this->reader->read($maxBytes, $cancellation);
        } catch (IO\Exception\RuntimeException) {
            $this->eof = true;
            return '';
        }

        if ($data === '' && $this->reader->reachedEndOfDataSource()) {
            $this->eof = true;
        }

        return $data;
    }

    /**
     * Whether the connection has closed and all data has been returned.
     */
    public function reachedEndOfDataSource(): bool
    {
        return $this->eof;
    }
}
