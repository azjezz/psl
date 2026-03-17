<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;

use function sprintf;
use function strlen;

/**
 * @require-implements ReadHandleInterface
 */
trait ReadHandleConvenienceMethodsTrait
{
    /**
     * Read until there is no more data to read.
     *
     * It is possible for this to never return, e.g. if called on a pipe
     * or socket which the other end keeps open forever. Set a timeout if you
     * do not want this to happen.
     *
     * Up to `$maxBytes` may be allocated in a buffer; large values may lead to
     * unnecessarily hitting the request memory limit.
     *
     * @param ?positive-int $maxBytes the maximum number of bytes to read
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    public function readAll(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        $toRead = $maxBytes;
        $data = '';

        do {
            /** @var positive-int|null $chunkSize */
            $chunkSize = $toRead;
            $chunk = $this->read($chunkSize, $cancellation);
            $data .= $chunk;
            if (null !== $toRead) {
                $toRead -= strlen($chunk);
            }
        } while ((null === $toRead || $toRead > 0) && !$this->reachedEndOfDataSource());

        return $data;
    }

    /**
     * Read a fixed amount of data.
     *
     * It is possible for this to never return, e.g. if called on a pipe
     * or socket which the other end keeps open forever. Set a timeout if you
     * do not want this to happen.
     *
     * @param positive-int $size the number of bytes to read.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    public function readFixedSize(
        int $size,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        $data = $this->readAll($size, $cancellation);
        $length = strlen($data);

        if ($length !== $size) {
            throw new Exception\RuntimeException(sprintf(
                '%d bytes were requested, but only able to read %d bytes',
                $size,
                $length,
            ));
        }

        return $data;
    }
}
