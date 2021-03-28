<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl;
use Psl\Asio;
use Psl\Str;

/**
 * @require-implements WriteHandleInterface
 */
trait WriteHandleConvenienceMethodsTrait
{
    /**
     * Write all of the requested data.
     *
     * A wrapper around `writeAsync()` that will:
     * - do multiple writes if necessary to write the entire provided buffer
     * - throws `Exception\RuntimeException` if it is not possible to write all the requested data
     *
     * It is possible for this to never return, e.g. if called on a pipe or
     * or socket which the other end keeps open forever. Set a timeout if you
     * do not want this to happen.
     *
     * @throws Psl\Exception\InvariantViolationException If $timeout_ms is negative.
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the write would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     * @throws Exception\TimeoutException If reached timeout before completing the operation.
     */
    public function writeAll(string $bytes, ?int $timeout_ms = null): void
    {
        if ($bytes === '') {
            return;
        }

        Psl\invariant(
            $timeout_ms === null || $timeout_ms > 0,
            '$timeout_ms must be null, or > 0',
        );

        $original_size = Str\Byte\length($bytes);
        $written = 0;

        $timer = new Internal\OptionalIncrementalTimeout(
            $timeout_ms,
            static function () use (&$written): void {
                throw new Exception\TimeoutException(Str\format(
                    "Reached timeout before %s data could be written.",
                    $written === 0 ? 'any' : 'all',
                ));
            },
        );

        do {
            $written = (int) Asio\await(Asio\async(fn () => $this->write(
                $bytes,
                $timer->getRemaining(),
            )));
            $bytes = Str\Byte\slice($bytes, $written);
        } while ($written !== 0 && $bytes !== '');

        if ($bytes !== '') {
            throw new Exception\RuntimeException(Str\format(
                "asked to write %d bytes, but only able to write %d bytes",
                $original_size,
                $original_size - Str\Byte\length($bytes),
            ));
        }
    }
}
