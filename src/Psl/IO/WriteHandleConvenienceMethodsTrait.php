<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl;
use Psl\DateTime\Duration;
use Psl\Str;

use function strlen;
use function substr;

/**
 * @require-implements WriteHandleInterface
 *
 * @mago-expect lint:no-else-clause
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
    public function writeAll(string $bytes, null|Duration $timeout = null): void
    {
        if ('' === $bytes) {
            return;
        }

        $original_size = strlen($bytes);

        if (null === $timeout) {
            do {
                $written = $this->write($bytes);
                $bytes = substr($bytes, $written);
            } while (0 !== $written && '' !== $bytes);
        } else {
            /**
             * @var Psl\Ref<int> $written_ref
             */
            $written_ref = new Psl\Ref(0);

            $timer = new Psl\Async\OptionalIncrementalTimeout($timeout, static function () use ($written_ref): void {
                // @codeCoverageIgnoreStart
                throw new Exception\TimeoutException(Str\format(
                    'Reached timeout before %s data could be written.',
                    0 === $written_ref->value ? 'any' : 'all',
                ));
                // @codeCoverageIgnoreEnd
            });

            do {
                $written_ref->value = $this->write($bytes, $timer->getRemaining());

                $bytes = substr($bytes, $written_ref->value);
            } while (0 !== $written_ref->value && '' !== $bytes);
        }

        if ('' !== $bytes) {
            // @codeCoverageIgnoreStart
            throw new Exception\RuntimeException(Str\format(
                'asked to write %d bytes, but only able to write %d bytes',
                $original_size,
                $original_size - strlen($bytes),
            ));
            // @codeCoverageIgnoreEnd
        }
    }
}
