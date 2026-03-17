<?php

declare(strict_types=1);

namespace Psl\Encoding\QuotedPrintable;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;

use function quoted_printable_decode;
use function sprintf;
use function str_ends_with;
use function strlen;
use function strpos;
use function substr;

/**
 * A read handle that decodes quoted-printable data from an inner readable handle.
 *
 * Reads lines from the inner handle, joins soft-break continuations (lines ending with `=`),
 * and decodes complete logical lines via {@see quoted_printable_decode()}.
 */
final class DecodingReadHandle implements IO\BufferedReadHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;

    private IO\Reader $reader;
    private string $buffer = '';
    private string $accumulated = '';
    private bool $eof = false;
    private bool $hardBreakPending = false;

    public function __construct(IO\ReadHandleInterface $handle)
    {
        $this->reader = new IO\Reader($handle);
    }

    public function reachedEndOfDataSource(): bool
    {
        return $this->eof && $this->buffer === '';
    }

    public function tryRead(null|int $maxBytes = null): string
    {
        if ($this->buffer === '' && !$this->eof) {
            $this->fillBuffer();
        }

        if ($this->buffer === '') {
            return '';
        }

        if (null === $maxBytes || $maxBytes >= strlen($this->buffer)) {
            $result = $this->buffer;
            $this->buffer = '';
            return $result;
        }

        $result = substr($this->buffer, 0, $maxBytes);
        $this->buffer = substr($this->buffer, $maxBytes);
        return $result;
    }

    public function read(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        if ($this->eof && $this->buffer === '') {
            return '';
        }

        if ($this->buffer === '') {
            $this->fillBuffer($cancellation);
        }

        return $this->tryRead($maxBytes);
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

            // @codeCoverageIgnoreStart
            return $line;
            // @codeCoverageIgnoreEnd
        }

        // No EOL found; return whatever remains, or null if empty
        // @codeCoverageIgnoreStart
        if ($this->buffer === '' && !$this->eof) {
            $this->fillBuffer($cancellation);
        }

        // @codeCoverageIgnoreEnd

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
        $suffixLen = strlen($suffix);
        $idx = strpos($this->buffer, $suffix);
        if ($idx !== false) {
            $result = substr($this->buffer, 0, $idx);
            $this->buffer = substr($this->buffer, $idx + $suffixLen);
            return $result;
        }

        while (!$this->eof) {
            $offset = strlen($this->buffer) - $suffixLen + 1;
            $offset = $offset > 0 ? $offset : 0;

            $this->fillBuffer($cancellation);

            $idx = strpos($this->buffer, $suffix, $offset);
            if ($idx !== false) {
                $result = substr($this->buffer, 0, $idx);
                $this->buffer = substr($this->buffer, $idx + $suffixLen);
                return $result;
            }
        }

        return null;
    }

    public function readUntilBounded(
        string $suffix,
        int $maxBytes,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): null|string {
        $suffixLen = strlen($suffix);
        $idx = strpos($this->buffer, $suffix);
        if ($idx !== false) {
            // @codeCoverageIgnoreStart
            if ($idx > $maxBytes) {
                throw new IO\Exception\OverflowException(sprintf(
                    'Exceeded maximum byte limit (%d) before encountering the suffix ("%s").',
                    $maxBytes,
                    $suffix,
                ));
            }

            $result = substr($this->buffer, 0, $idx);
            $this->buffer = substr($this->buffer, $idx + $suffixLen);
            return $result;
            // @codeCoverageIgnoreEnd
        }

        // @codeCoverageIgnoreStart
        if (strlen($this->buffer) > $maxBytes) {
            throw new IO\Exception\OverflowException(sprintf(
                'Exceeded maximum byte limit (%d) before encountering the suffix ("%s").',
                $maxBytes,
                $suffix,
            ));
        }

        // @codeCoverageIgnoreEnd

        while (!$this->eof) {
            $offset = strlen($this->buffer) - $suffixLen + 1;
            $offset = $offset > 0 ? $offset : 0;

            $this->fillBuffer($cancellation);

            $idx = strpos($this->buffer, $suffix, $offset);
            if ($idx !== false) {
                // @codeCoverageIgnoreStart
                if ($idx > $maxBytes) {
                    throw new IO\Exception\OverflowException(sprintf(
                        'Exceeded maximum byte limit (%d) before encountering the suffix ("%s").',
                        $maxBytes,
                        $suffix,
                    ));
                }

                // @codeCoverageIgnoreEnd

                $result = substr($this->buffer, 0, $idx);
                $this->buffer = substr($this->buffer, $idx + $suffixLen);
                return $result;
            }

            // @codeCoverageIgnoreStart
            if (strlen($this->buffer) > $maxBytes) {
                throw new IO\Exception\OverflowException(sprintf(
                    'Exceeded maximum byte limit (%d) before encountering the suffix ("%s").',
                    $maxBytes,
                    $suffix,
                ));
            }
            // @codeCoverageIgnoreEnd
        }

        return null;
    }

    private function fillBuffer(CancellationTokenInterface $cancellation = new NullCancellationToken()): void
    {
        if ($this->eof) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        while (true) {
            $line = $this->reader->readUntil("\n", $cancellation);
            if ($line === null) {
                $remaining = $this->reader->read(null, $cancellation);
                if ($remaining !== '') {
                    $this->accumulated .= str_ends_with($remaining, "\r") ? substr($remaining, 0, -1) : $remaining;
                }

                if ($this->accumulated !== '') {
                    if ($this->hardBreakPending) {
                        $this->buffer .= "\r\n";
                    }

                    $this->buffer .= quoted_printable_decode($this->accumulated);
                    $this->accumulated = '';
                }

                $this->eof = true;

                return;
            }

            $line = str_ends_with($line, "\r") ? substr($line, 0, -1) : $line;

            if (str_ends_with($line, '=')) {
                /** @var non-negative-int $len */
                $len = strlen($line) - 1;
                $this->accumulated .= substr($line, 0, $len);

                continue;
            }

            $this->accumulated .= $line;

            if ($this->hardBreakPending) {
                $this->buffer .= "\r\n";
            }

            $this->buffer .= quoted_printable_decode($this->accumulated);
            $this->accumulated = '';
            $this->hardBreakPending = true;

            return;
        }
    }
}
