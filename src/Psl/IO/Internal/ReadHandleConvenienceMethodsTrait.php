<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

use Psl;
use Psl\Asio;
use Psl\IO\Exception;
use Psl\Math;
use Psl\Str;

/**
 * @require-implements Psl\IO\ReadHandleInterface
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
     * @throws Psl\Exception\InvariantViolationException If $max_bytes is 0, or $timeout_ms is negative.
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the read would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If $timeout_ms is reached before being able to read from the handle.
     */
    public function readAll(?int $max_bytes = null, ?int $timeout_ms = null): string
    {
        Psl\invariant($max_bytes === null || $max_bytes > 0, '$max_bytes must be null, or > 0');
        Psl\invariant($timeout_ms === null || $timeout_ms > 0, '$timeout_ms must be null, or > 0');

        $to_read = $max_bytes ?? Math\INT64_MAX;

        $data = '';
        $timer = new OptionalIncrementalTimeout(
            $timeout_ms,
            static function () use (&$data): void {
                throw new Exception\TimeoutException(Str\format(
                    "Reached timeout before %s data could be read.",
                    $data === '' ? 'any' : 'all',
                ));
            },
        );

        do {
            $chunk_size = $to_read;
            $chunk = $this->read($chunk_size, $timer->getRemaining());
            /** 
             * @var string $data
             */
            $data .= $chunk;
            $to_read -= Str\Byte\length($chunk);
        } while ($to_read > 0 && $chunk !== '');

        return $data;
    }

    /**
     * Read a fixed amount of data.
     *
     * It is possible for this to never return, e.g. if called on a pipe or
     * or socket which the other end keeps open forever. Set a timeout if you
     * do not want this to happen.
     *
     * @throws Psl\Exception\InvariantViolationException If $max_bytes is 0, or $timeout_ms is negative.
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the read would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If $timeout_ms is reached before being able to read from the handle.
     */
    public function readFixedSize(int $size, ?int $timeout_ms = null): string
    {
        $data = Asio\await(Asio\async(fn () => $this->readAll($size, $timeout_ms)));
        if (Str\Byte\length($data) !== $size) {
            throw new Exception\RuntimeException(Str\format(
                "%d bytes were requested, but only able to read %d bytes",
                $size,
                Str\Byte\length($data),
            ));
        }

        return $data;
    }
}
