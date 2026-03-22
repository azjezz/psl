<?php

declare(strict_types=1);

namespace Psl\MIME\MultiPart;

use Generator;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;
use Psl\MIME\Exception\MultiPartException;
use Psl\MIME\Part\PartInterface;

/**
 * Contract for streaming multipart body parsers.
 *
 * Implementations parse a multipart body from a readable stream and yield
 * {@see PartInterface} instances lazily via a generator. This allows callers
 * to process parts one at a time without buffering the entire body in memory.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2046#section-5.1
 *
 * @see Parser for the default streaming implementation with spool-to-disk support.
 *
 * @api
 */
interface ParserInterface
{
    /**
     * Parse a multipart body from a readable stream.
     *
     * Yields {@see PartInterface} instances lazily as each part's headers and body
     * are fully read from the input stream. The generator terminates when the closing
     * boundary delimiter is encountered or the stream ends.
     *
     * @param IO\ReadHandleInterface $handle The input stream containing the multipart body (without the Content-Type header).
     * @param CancellationTokenInterface $cancellation Token to cancel the parsing operation cooperatively.
     *
     * @return Generator<int, PartInterface, void, void>
     *
     * @throws MultiPartException If the multipart body is malformed or exceeds configured limits.
     */
    public function parse(
        IO\ReadHandleInterface $handle,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): Generator;
}
