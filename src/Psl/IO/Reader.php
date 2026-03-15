<?php

declare(strict_types=1);

namespace Psl\IO;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\Str;

use function strlen;
use function strpos;
use function substr;

final class Reader implements BufferedReadHandleInterface
{
    use ReadHandleConvenienceMethodsTrait;

    private readonly ReadHandleInterface $handle;

    private bool $eof = false;
    private string $buffer = '';

    public function __construct(ReadHandleInterface $handle)
    {
        $this->handle = $handle;
    }

    /**
     * {@inheritDoc}
     *
     * @mago-expect lint:no-empty-catch-clause
     */
    #[Override]
    public function reachedEndOfDataSource(): bool
    {
        if ($this->eof) {
            return true;
        }

        if ('' !== $this->buffer) {
            return false;
        }

        // @codeCoverageIgnoreStart
        try {
            $this->buffer = $this->handle->read();
            if ('' === $this->buffer) {
                return $this->eof = $this->handle->reachedEndOfDataSource();
            }
        } catch (Exception\ExceptionInterface) {
            // ignore; it'll be thrown again when attempting a real read.
        }

        // @codeCoverageIgnoreEnd

        return false;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function readFixedSize(
        int $size,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        do {
            $length = strlen($this->buffer);
            if ($length >= $size || $this->eof) {
                break;
            }

            /** @var positive-int $to_read */
            $to_read = $size - $length;
            $this->fillBuffer($to_read, $cancellation);
        } while (true);

        if ($this->eof) {
            throw new Exception\RuntimeException('Reached end of file before requested size.');
        }

        $buffer_size = strlen($this->buffer);
        if ($size === $buffer_size) {
            $ret = $this->buffer;
            $this->buffer = '';
            return $ret;
        }

        $ret = substr($this->buffer, 0, $size);
        $this->buffer = substr($this->buffer, $size);
        return $ret;
    }

    /**
     * Read a single byte from the handle.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation, or reached end of file.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    public function readByte(CancellationTokenInterface $cancellation = new NullCancellationToken()): string
    {
        if ('' === $this->buffer && !$this->eof) {
            $this->fillBuffer(null, $cancellation);
        }

        if ('' === $this->buffer) {
            throw new Exception\RuntimeException('Reached EOF without any more data.');
        }

        $ret = $this->buffer[0];
        if ($ret === $this->buffer) {
            $this->buffer = '';
            return $ret;
        }

        $this->buffer = substr($this->buffer, 1);
        return $ret;
    }

    /**
     * {@inheritDoc}
     */
    public function readLine(CancellationTokenInterface $cancellation = new NullCancellationToken()): null|string
    {
        $line = $this->readUntil("\n", $cancellation);
        if (null !== $line) {
            if ($line !== '' && $line[-1] === "\r") {
                return substr($line, 0, -1);
            }

            return $line;
        }

        $content = $this->read(null, $cancellation);
        return '' === $content ? null : $content;
    }

    /**
     * Read until the specified suffix is seen.
     *
     * The trailing suffix is read (so won't be returned by other calls), but is not
     * included in the return value.
     *
     * This call returns null if the suffix is not seen, even if there is other
     * data.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    public function readUntil(
        string $suffix,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): null|string {
        $buf = $this->buffer;
        $idx = strpos($buf, $suffix);
        $suffix_len = strlen($suffix);
        if (false !== $idx) {
            $this->buffer = substr($buf, $idx + $suffix_len);
            return substr($buf, 0, $idx);
        }

        do {
            // + 1 as it would have been matched in the previous iteration if it
            // fully fit in the chunk
            $offset = strlen($buf) - $suffix_len + 1;
            $offset = $offset > 0 ? $offset : 0;
            $chunk = $this->handle->read(null, $cancellation);
            if ('' === $chunk) {
                $this->buffer = $buf;
                return null;
            }

            $buf .= $chunk;
            $idx = strpos($buf, $suffix, $offset);
        } while (false === $idx);

        $this->buffer = substr($buf, $idx + $suffix_len);

        return substr($buf, 0, $idx);
    }

    /**
     * Read until the specified suffix is seen, with a maximum number of bytes to read.
     *
     * The trailing suffix is read (so won't be returned by other calls), but is not
     * included in the return value.
     *
     * This call returns null if the suffix is not seen before EOF.
     *
     * @param positive-int $max_bytes Maximum number of bytes to read before throwing OverflowException.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws CancelledException If the cancellation token is cancelled.
     * @throws Exception\OverflowException If $max_bytes is exceeded without finding the suffix.
     */
    public function readUntilBounded(
        string $suffix,
        int $max_bytes,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): null|string {
        $buf = $this->buffer;
        $suffix_len = strlen($suffix);
        $idx = strpos($buf, $suffix);
        if (false !== $idx) {
            if ($idx > $max_bytes) {
                throw new Exception\OverflowException(Str\format(
                    'Exceeded maximum byte limit (%d) before encountering the suffix ("%s").',
                    $max_bytes,
                    $suffix,
                ));
            }

            $this->buffer = substr($buf, $idx + $suffix_len);
            return substr($buf, 0, $idx);
        }

        if (strlen($buf) > $max_bytes) {
            throw new Exception\OverflowException(Str\format(
                'Exceeded maximum byte limit (%d) before encountering the suffix ("%s").',
                $max_bytes,
                $suffix,
            ));
        }

        do {
            $offset = strlen($buf) - $suffix_len + 1;
            $offset = $offset > 0 ? $offset : 0;
            $chunk = $this->handle->read(null, $cancellation);
            if ('' === $chunk) {
                $this->buffer = $buf;
                return null;
            }

            $buf .= $chunk;
            $idx = strpos($buf, $suffix, $offset);

            if (false !== $idx) {
                if ($idx > $max_bytes) {
                    $this->buffer = $buf;
                    throw new Exception\OverflowException(Str\format(
                        'Exceeded maximum byte limit (%d) before encountering the suffix ("%s").',
                        $max_bytes,
                        $suffix,
                    ));
                }

                break;
            }

            if (strlen($buf) > $max_bytes) {
                $this->buffer = $buf;
                throw new Exception\OverflowException(Str\format(
                    'Exceeded maximum byte limit (%d) before encountering the suffix ("%s").',
                    $max_bytes,
                    $suffix,
                ));
            }
        } while (true);

        /** @var int<0, max> $idx*/
        $this->buffer = substr($buf, $idx + $suffix_len);

        return substr($buf, 0, $idx);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function read(
        null|int $max_bytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        if ($this->eof) {
            return '';
        }

        if ('' === $this->buffer) {
            $this->fillBuffer(null, $cancellation);
        }

        // We either have a buffer, or reached EOF; either way, behavior matches
        // read, so just delegate
        return $this->tryRead($max_bytes);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function tryRead(null|int $max_bytes = null): string
    {
        if ($this->eof) {
            return '';
        }

        if ('' === $this->buffer) {
            $this->buffer = $this->getHandle()->tryRead();
            if ('' === $this->buffer) {
                return '';
            }
        }

        $buffer = $this->buffer;
        if (null === $max_bytes || $max_bytes >= strlen($buffer)) {
            $this->buffer = '';
            return $buffer;
        }

        $this->buffer = substr($buffer, $max_bytes);

        return substr($buffer, 0, $max_bytes);
    }

    public function getHandle(): ReadHandleInterface
    {
        return $this->handle;
    }

    /**
     * @param null|positive-int $desired_bytes
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    private function fillBuffer(null|int $desired_bytes, CancellationTokenInterface $cancellation): void
    {
        $chunk = $this->handle->read($desired_bytes, $cancellation);
        $this->buffer .= $chunk;
        if ('' === $chunk) {
            $this->eof = $this->handle->reachedEndOfDataSource();
        }
    }
}
