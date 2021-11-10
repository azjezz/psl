<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl;
use Psl\Str;

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
     * @throws Exception\TimeoutException If reached timeout before completing the operation.
     */
    public function writeAll(string $bytes, ?float $timeout = null): void
    {
        if ($bytes === '') {
            return;
        }

        $original_size = strlen($bytes);
        /**
         * @var Psl\Ref<int> $written
         */
        $written = new Psl\Ref(0);

        $timer = new Internal\OptionalIncrementalTimeout(
            $timeout,
            static function () use ($written): void {
                // @codeCoverageIgnoreStart
                throw new Exception\TimeoutException(Str\format(
                    "Reached timeout before %s data could be written.",
                    ($written->value === 0) ? 'any' : 'all',
                ));
                // @codeCoverageIgnoreEnd
            },
        );

        do {
            $written->value = $this->write(
                $bytes,
                $timer->getRemaining(),
            );

            $bytes = substr($bytes, $written->value);
        } while ($written->value !== 0 && $bytes !== '');

        if ($bytes !== '') {
            // @codeCoverageIgnoreStart
            throw new Exception\RuntimeException(Str\format(
                "asked to write %d bytes, but only able to write %d bytes",
                $original_size,
                $original_size - strlen($bytes),
            ));
            // @codeCoverageIgnoreEnd
        }
    }
}
