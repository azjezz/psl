<?php

declare(strict_types=1);

namespace Psl\Encoding\QuotedPrintable;

use Psl;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;

use function str_ends_with;
use function strlen;
use function strpos;
use function substr;

/**
 * A read handle that encodes raw text from an inner readable handle using quoted-printable encoding.
 *
 * Reads line-by-line from the inner handle and encodes each line via {@see encode_line()}.
 */
final class EncodingReadHandle implements IO\BufferedReadHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;

    private IO\Reader $reader;
    private string $buffer = '';
    private bool $eof = false;
    private bool $firstLine = true;

    public function __construct(IO\ReadHandleInterface $handle)
    {
        $this->reader = new IO\Reader($handle);
    }

    public function reachedEndOfDataSource(): bool
    {
        return $this->eof && $this->buffer === '';
    }

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

        return $this->tryRead($max_bytes);
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
        $line = $this->readUntil("\n", $cancellation);
        if ($line !== null) {
            if ($line !== '' && $line[-1] === "\r") {
                return substr($line, 0, -1);
            }

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

    private function fillBuffer(CancellationTokenInterface $cancellation = new NullCancellationToken()): void
    {
        if ($this->eof) {
            return;
        }

        $line = $this->reader->readUntil("\n", $cancellation);
        if ($line !== null) {
            $line = str_ends_with($line, "\r") ? substr($line, 0, -1) : $line;

            if (!$this->firstLine) {
                $this->buffer .= "\r\n";
            }

            $this->buffer .= encode_line($line);
            $this->firstLine = false;

            return;
        }

        $remaining = $this->reader->read(null, $cancellation);
        if ($remaining !== '') {
            $remaining = str_ends_with($remaining, "\r") ? substr($remaining, 0, -1) : $remaining;

            if (!$this->firstLine) {
                $this->buffer .= "\r\n";
            }

            $this->buffer .= encode_line($remaining);
        }

        $this->eof = true;
    }
}
