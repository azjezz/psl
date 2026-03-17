<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;

use function sprintf;
use function strlen;
use function substr;

/**
 * @require-implements WriteHandleInterface
 */
trait WriteHandleConvenienceMethodsTrait
{
    /**
     * Write all of the requested data.
     *
     * A wrapper around `write()` that will:
     * - do multiple writes if necessary to write the entire provided buffer
     * - throws `Exception\RuntimeException` if it is not possible to write all the requested data
     *
     * It is possible for this to never return, e.g. if called on a pipe or
     * or socket which the other end keeps open forever. Set a timeout if you
     * do not want this to happen.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    public function writeAll(
        string $bytes,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void {
        if ('' === $bytes) {
            return;
        }

        $originalSize = strlen($bytes);

        do {
            $written = $this->write($bytes, $cancellation);
            $bytes = substr($bytes, $written);
        } while (0 !== $written && '' !== $bytes);

        if ('' !== $bytes) {
            // @codeCoverageIgnoreStart
            throw new Exception\RuntimeException(sprintf(
                'asked to write %d bytes, but only able to write %d bytes',
                $originalSize,
                $originalSize - strlen($bytes),
            ));
            // @codeCoverageIgnoreEnd
        }
    }
}
