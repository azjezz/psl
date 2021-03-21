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
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the read would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If reached timeout.
     * @throws InvariantViolationException If $max_bytes is 0, or $timeout_ms is negative.
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
            /** @var string $chunk */
            $chunk = Asio\await(Asio\async(fn () => $this->read(
                $chunk_size,
                $timer->getRemaining(),
            )));
            $data .= $chunk;
            $to_read -= Str\Byte\length($chunk);
        } while ($to_read > 0 && $chunk !== '');

        return $data;
    }

    /**
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the read would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If reached timeout.
     * @throws InvariantViolationException If $max_bytes is 0, or $timeout_ms is negative.
     */
    public function readFixedSize(int $size, ?int $timeout_ms = null): string
    {
        /** @var string $data */
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
