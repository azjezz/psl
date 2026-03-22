<?php

declare(strict_types=1);

namespace Psl\MIME\Sniff;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\IO;
use Psl\MIME\Exception\InvalidMediaTypeComponentException;
use Psl\MIME\Exception\ParsingException;
use Psl\MIME\MediaType;

/**
 * Detect the MIME type from a seekable read handle without consuming its contents.
 *
 * Reads up to {@see Internal\SNIFF_BUFFER_SIZE} bytes (4096) from the current position,
 * delegates to {@see from_string()} for detection, then seeks back to the original
 * position so the handle state is not modified.
 *
 * @throws ParsingException
 * @throws InvalidMediaTypeComponentException
 * @throws CancelledException If the cancellation token is triggered before or during the read.
 *
 * @api
 */
function from_handle(
    IO\ReadHandleInterface&IO\SeekHandleInterface $handle,
    CancellationTokenInterface $cancellation = new NullCancellationToken(),
): MediaType {
    $cancellation->throwIfCancelled();
    $position = $handle->tell();
    $content = $handle->readAll(Internal\SNIFF_BUFFER_SIZE, $cancellation);
    $handle->seek($position);

    return namespace\from_string($content);
}
