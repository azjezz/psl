<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\Str;

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
     * Up to `$max_bytes` may be allocated in a buffer; large values may lead to
     * unnecessarily hitting the request memory limit.
     *
     * @param ?positive-int $max_bytes the maximum number of bytes to read
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    public function readAll(
        null|int $max_bytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        $to_read = $max_bytes;
        $data = '';

        do {
            /** @var positive-int|null $chunk_size */
            $chunk_size = $to_read;
            $chunk = $this->read($chunk_size, $cancellation);
            $data .= $chunk;
            if (null !== $to_read) {
                $to_read -= strlen($chunk);
            }
        } while ((null === $to_read || $to_read > 0) && !$this->reachedEndOfDataSource());

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
            throw new Exception\RuntimeException(Str\format(
                '%d bytes were requested, but only able to read %d bytes',
                $size,
                $length,
            ));
        }

        return $data;
    }
}
