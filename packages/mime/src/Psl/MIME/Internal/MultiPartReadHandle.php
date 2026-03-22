<?php

declare(strict_types=1);

namespace Psl\MIME\Internal;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;
use Psl\MIME\Part\PartInterface;

use function count;
use function strlen;
use function substr;

/**
 * Streaming read handle that lazily serializes multipart body content.
 *
 * Reads from each part in sequence, emitting boundary delimiters and headers
 * on demand without buffering the entire multipart body.
 *
 * @internal
 */
final class MultiPartReadHandle implements IO\ReadHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;

    /**
     * State: about to emit a boundary delimiter before the next part.
     */
    private const int STATE_BOUNDARY = 0;

    /**
     * State: emitting the MIME headers for the current part.
     */
    private const int STATE_HEADERS = 1;

    /**
     * State: streaming the body content of the current part.
     */
    private const int STATE_BODY = 2;

    /**
     * State: emitting the closing boundary delimiter.
     */
    private const int STATE_CLOSING = 3;

    /**
     * State: all content has been emitted.
     */
    private const int STATE_EOF = 4;

    /**
     * Current state machine state.
     *
     * @var int<0, 4>
     */
    private int $state;

    /**
     * Index of the part currently being serialized.
     *
     * @var int<0, max>
     */
    private int $partIndex = 0;

    /**
     * Buffered bytes waiting to be returned by the next read call.
     */
    private string $buffer = '';

    /**
     * Whether the first part boundary (without leading CRLF) has been emitted.
     */
    private bool $firstPart = true;

    /**
     * Cached body handle for the current part, to avoid recreating on each read.
     */
    private null|IO\ReadHandleInterface $currentBody = null;

    /**
     * @param non-empty-string $boundary
     * @param list<PartInterface> $parts
     */
    public function __construct(
        private readonly string $boundary,
        private readonly array $parts,
    ) {
        $this->state = $this->parts === [] ? self::STATE_CLOSING : self::STATE_BOUNDARY;
    }

    /**
     * Attempt a non-blocking read, advancing through the state machine as needed.
     */
    public function tryRead(null|int $maxBytes = null): string
    {
        if ($this->state === self::STATE_EOF) {
            return '';
        }

        // Drain buffer first
        if ($this->buffer !== '') {
            return $this->drainBuffer($maxBytes);
        }

        return match ($this->state) {
            self::STATE_BOUNDARY => $this->enterBoundary($maxBytes),
            self::STATE_HEADERS => $this->enterHeaders($maxBytes),
            self::STATE_BODY => $this->readBodyTry($maxBytes),
            self::STATE_CLOSING => $this->enterClosing($maxBytes),
        };
    }

    /**
     * Read with cancellation support, advancing through the state machine as needed.
     */
    public function read(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        if ($this->state === self::STATE_EOF) {
            return '';
        }

        // Drain buffer first
        if ($this->buffer !== '') {
            return $this->drainBuffer($maxBytes);
        }

        return match ($this->state) {
            self::STATE_BOUNDARY => $this->enterBoundary($maxBytes),
            self::STATE_HEADERS => $this->enterHeaders($maxBytes),
            self::STATE_BODY => $this->readBody($maxBytes, $cancellation),
            self::STATE_CLOSING => $this->enterClosing($maxBytes),
        };
    }

    /**
     * Returns true when the closing boundary has been fully emitted and the buffer is empty.
     */
    public function reachedEndOfDataSource(): bool
    {
        return $this->state === self::STATE_EOF && $this->buffer === '';
    }

    /**
     * Buffer the boundary delimiter for the current part and transition to {@see self::STATE_HEADERS}.
     */
    private function enterBoundary(null|int $maxBytes): string
    {
        $delimiter = $this->firstPart ? '--' . $this->boundary . "\r\n" : "\r\n--" . $this->boundary . "\r\n";

        $this->firstPart = false;
        $this->buffer = $delimiter;
        $this->state = self::STATE_HEADERS;

        return $this->drainBuffer($maxBytes);
    }

    /**
     * Serialize the current part's headers into the buffer and transition to {@see self::STATE_BODY}.
     */
    private function enterHeaders(null|int $maxBytes): string
    {
        $part = $this->parts[$this->partIndex];
        $headerString = '';

        foreach ($part->headers->pairs() as [$name, $value]) {
            $headerString .= $name . ': ' . $value . "\r\n";
        }

        $headerString .= "\r\n";

        $this->buffer = $headerString;
        $this->state = self::STATE_BODY;

        return $this->drainBuffer($maxBytes);
    }

    /**
     * Non-blocking body read for the current part; advances to the next part when exhausted.
     *
     * @param int<1, max>|null $maxBytes
     */
    private function readBodyTry(null|int $maxBytes): string
    {
        $this->currentBody ??= $this->parts[$this->partIndex]->body();

        $chunk = $this->currentBody->tryRead($maxBytes);

        if ($chunk === '' && $this->currentBody->reachedEndOfDataSource()) {
            $this->advancePart();
            return '';
        }

        return $chunk;
    }

    /**
     * Blocking body read for the current part with cancellation support;
     * advances to the next part when exhausted.
     *
     * @param int<1, max>|null $maxBytes
     */
    private function readBody(null|int $maxBytes, CancellationTokenInterface $cancellation): string
    {
        $this->currentBody ??= $this->parts[$this->partIndex]->body();
        $body = $this->currentBody;

        $chunk = $body->read($maxBytes, $cancellation);

        if ($chunk === '' && $body->reachedEndOfDataSource()) {
            $this->advancePart();
            return '';
        }

        return $chunk;
    }

    /**
     * Move to the next part, or to closing state if all parts have been emitted.
     */
    private function advancePart(): void
    {
        $this->currentBody = null;
        $this->partIndex++;

        if ($this->partIndex >= count($this->parts)) {
            $this->state = self::STATE_CLOSING;
        } else {
            $this->state = self::STATE_BOUNDARY;
        }
    }

    /**
     * Buffer the closing boundary delimiter ("--boundary--") and transition to {@see self::STATE_EOF}.
     */
    private function enterClosing(null|int $maxBytes): string
    {
        $this->buffer = "\r\n--" . $this->boundary . "--\r\n";
        $this->state = self::STATE_EOF;

        return $this->drainBuffer($maxBytes);
    }

    /**
     * Return up to $maxBytes from the internal buffer, removing consumed bytes.
     */
    private function drainBuffer(null|int $maxBytes): string
    {
        if ($maxBytes === null || $maxBytes >= strlen($this->buffer)) {
            $result = $this->buffer;
            $this->buffer = '';
            return $result;
        }

        $result = substr($this->buffer, 0, $maxBytes);
        $this->buffer = substr($this->buffer, $maxBytes);

        return $result;
    }
}
