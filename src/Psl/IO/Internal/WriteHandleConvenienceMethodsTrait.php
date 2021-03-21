<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

use Psl;
use Psl\Asio;
use Psl\IO\Exception;
use Psl\Str;

trait WriteHandleConvenienceMethodsTrait
{
    public function writeAll(string $data, ?int $timeout_ms = null): void
    {
        if ($data === '') {
            return;
        }

        Psl\invariant(
            $timeout_ms === null || $timeout_ms > 0,
            '$timeout_ms must be null, or > 0',
        );

        $original_size = Str\Byte\length($data);
        $written = 0;

        $timer = new OptionalIncrementalTimeout(
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
                $data,
                $timer->getRemaining(),
            )));
            $data = Str\Byte\slice($data, $written);
        } while ($written !== 0 && $data !== '');

        if ($data !== '') {
            throw new Exception\RuntimeException(Str\format(
                "asked to write %d bytes, but only able to write %d bytes",
                $original_size,
                $original_size - Str\Byte\length($data),
            ));
        }
    }
}
