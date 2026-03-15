<?php

declare(strict_types=1);

namespace Psl\Encoding\Hex;

use Psl;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\Encoding\Exception;
use Psl\IO;

use function strlen;
use function strpos;
use function substr;

use const PHP_EOL;

/**
 * A read handle that decodes hex-encoded data from an inner readable handle.
 *
 * Reads chunks from the inner handle and decodes complete 2-byte hex pairs.
 * Buffers any odd trailing character for the next read.
 * On EOF, throws if there is an incomplete hex pair remaining.
 */
final class DecodingReadHandle implements IO\BufferedReadHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;

    private string $buffer = '';
    private string $remainder = '';
    private bool $eof = false;

    public function __construct(
        private readonly IO\ReadHandleInterface $handle,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function reachedEndOfDataSource(): bool
    {
        return $this->eof && $this->buffer === '';
    }

    /**
     * {@inheritDoc}
     */
    public function tryRead(null|int $max_bytes = null): string
    {
        if ($this->buffer === '' && !$this->eof) {
            $this->fillBuffer();
        }

        if ($this->buffer === '') {
            return '';
        }

        if (null === $max_bytes || $max_bytes >= strlen($this->buffer)) {
            $result = $this->buffer;
            $this->buffer = '';
            return $result;
        }

        $result = substr($this->buffer, 0, $max_bytes);
        $this->buffer = substr($this->buffer, $max_bytes);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function read(
        null|int $max_bytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        if ($this->eof && $this->buffer === '') {
            return '';
        }

        if ($this->buffer === '') {
            $this->fillBuffer($cancellation);
        }

        if ($this->buffer === '') {
            return '';
        }

        if (null === $max_bytes || $max_bytes >= strlen($this->buffer)) {
            $result = $this->buffer;
            $this->buffer = '';
            return $result;
        }

        $result = substr($this->buffer, 0, $max_bytes);
        $this->buffer = substr($this->buffer, $max_bytes);
        return $result;
    }

    public function readByte(CancellationTokenInterface $cancellation = new NullCancellationToken()): string
    {
        if ($this->buffer === '' && !$this->eof) {
            $this->fillBuffer($cancellation);
        }

        if ($this->buffer === '') {
            throw new IO\Exception\RuntimeException('Reached EOF without any more data.');
        }

        $ret = $this->buffer[0];
        if ($ret === $this->buffer) {
            $this->buffer = '';
            return $ret;
        }

        $this->buffer = substr($this->buffer, 1);
        return $ret;
    }

    public function readLine(CancellationTokenInterface $cancellation = new NullCancellationToken()): null|string
    {
        $line = $this->readUntil(PHP_EOL, $cancellation);
        if ($line !== null) {
            return $line;
        }

        // No EOL found; return whatever remains, or null if empty
        if ($this->buffer === '' && !$this->eof) {
            $this->fillBuffer($cancellation);
        }

        if ($this->buffer === '') {
            return null;
        }

        $result = $this->buffer;
        $this->buffer = '';
        return $result;
    }

    public function readUntil(
        string $suffix,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): null|string {
        $suffix_len = strlen($suffix);
        $idx = strpos($this->buffer, $suffix);
        if ($idx !== false) {
            $result = substr($this->buffer, 0, $idx);
            $this->buffer = substr($this->buffer, $idx + $suffix_len);
            return $result;
        }

        while (!$this->eof) {
            $offset = strlen($this->buffer) - $suffix_len + 1;
            $offset = $offset > 0 ? $offset : 0;

            $this->fillBuffer($cancellation);

            $idx = strpos($this->buffer, $suffix, $offset);
            if ($idx !== false) {
                $result = substr($this->buffer, 0, $idx);
                $this->buffer = substr($this->buffer, $idx + $suffix_len);
                return $result;
            }
        }

        return null;
    }

    public function readUntilBounded(
        string $suffix,
        int $max_bytes,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): null|string {
        $suffix_len = strlen($suffix);
        $idx = strpos($this->buffer, $suffix);
        if ($idx !== false) {
            if ($idx > $max_bytes) {
                throw new IO\Exception\OverflowException(Psl\Str\format(
                    'Exceeded maximum byte limit (%d) before encountering the suffix ("%s").',
                    $max_bytes,
                    $suffix,
                ));
            }

            $result = substr($this->buffer, 0, $idx);
            $this->buffer = substr($this->buffer, $idx + $suffix_len);
            return $result;
        }

        if (strlen($this->buffer) > $max_bytes) {
            throw new IO\Exception\OverflowException(Psl\Str\format(
                'Exceeded maximum byte limit (%d) before encountering the suffix ("%s").',
                $max_bytes,
                $suffix,
            ));
        }

        while (!$this->eof) {
            $offset = strlen($this->buffer) - $suffix_len + 1;
            $offset = $offset > 0 ? $offset : 0;

            $this->fillBuffer($cancellation);

            $idx = strpos($this->buffer, $suffix, $offset);
            if ($idx !== false) {
                if ($idx > $max_bytes) {
                    throw new IO\Exception\OverflowException(Psl\Str\format(
                        'Exceeded maximum byte limit (%d) before encountering the suffix ("%s").',
                        $max_bytes,
                        $suffix,
                    ));
                }

                $result = substr($this->buffer, 0, $idx);
                $this->buffer = substr($this->buffer, $idx + $suffix_len);
                return $result;
            }

            if (strlen($this->buffer) > $max_bytes) {
                throw new IO\Exception\OverflowException(Psl\Str\format(
                    'Exceeded maximum byte limit (%d) before encountering the suffix ("%s").',
                    $max_bytes,
                    $suffix,
                ));
            }
        }

        return null;
    }

    /**
     * @throws Exception\RangeException If the hex data contains invalid characters or has an odd length at EOF.
     */
    private function fillBuffer(CancellationTokenInterface $cancellation = new NullCancellationToken()): void
    {
        if ($this->eof) {
            return;
        }

        $chunk = $this->handle->read(4096, $cancellation);
        if ($chunk === '' && $this->handle->reachedEndOfDataSource()) {
            $this->eof = true;
            // Decode any remaining bytes.
            if ($this->remainder !== '') {
                $this->buffer .= decode($this->remainder);
                $this->remainder = '';
            }

            return;
        }

        $data = $this->remainder . $chunk;

        // Decode complete 2-byte hex pairs only.
        $length = strlen($data);
        $usable = $length - ($length % 2);

        if ($usable > 0) {
            $this->buffer .= decode(substr($data, 0, $usable));
            $this->remainder = substr($data, $usable);
        } else {
            $this->remainder = $data;
        }
    }
}
