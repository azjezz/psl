<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl;
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
     * It is possible for this to never return, e.g. if called on a pipe or
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
     * @throws Exception\TimeoutException If $timeout is reached before being able to read from the handle.
     */
    public function readAll(?int $max_bytes = null, ?float $timeout = null): string
    {
        $to_read = $max_bytes;

        /** @var Psl\Ref<string> $data */
        $data = new Psl\Ref('');
        $timer = new Internal\OptionalIncrementalTimeout(
            $timeout,
            static function () use ($data): void {
                // @codeCoverageIgnoreStart
                throw new Exception\TimeoutException(Str\format(
                    "Reached timeout before %s data could be read.",
                    ($data->value === '') ? 'any' : 'all',
                ));
                // @codeCoverageIgnoreEnd
            },
        );

        do {
            $chunk_size = $to_read;
            /**
             * @var positive-int|null $chunk_size
             *
             * @psalm-suppress UnnecessaryVarAnnotation
             */
            $chunk = $this->read($chunk_size, $timer->getRemaining());
            $data->value .= $chunk;
            if ($to_read !== null) {
                $to_read -= strlen($chunk);
            }
        } while (($to_read === null || $to_read > 0) && $chunk !== '');

        return $data->value;
    }

    /**
     * Read a fixed amount of data.
     *
     * It is possible for this to never return, e.g. if called on a pipe or
     * or socket which the other end keeps open forever. Set a timeout if you
     * do not want this to happen.
     *
     * @param positive-int $size the number of bytes to read.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If $timeout is reached before being able to read from the handle.
     */
    public function readFixedSize(int $size, ?float $timeout = null): string
    {
        $data = $this->readAll($size, $timeout);

        if (($length = strlen($data)) !== $size) {
            throw new Exception\RuntimeException(Str\format(
                "%d bytes were requested, but only able to read %d bytes",
                $size,
                $length,
            ));
        }

        return $data;
    }
}
