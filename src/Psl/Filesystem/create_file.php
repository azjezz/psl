<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl\Internal;
use Psl\Math;
use Psl\Str;

use function touch;

/**
 * Create the file specified by $filename.
 *
 * @param non-empty-string $filename
 * @param int|null $time The touch time as a Unix timestamp.
 *                       Defaults to the current system time.
 * @param int|null $access_time The access time as a Unix timestamp.
 *                              Defaults to the current system time.
 *
 * @throws Exception\RuntimeException If unable to create the file.
 */
function create_file(string $filename, ?int $time = null, ?int $access_time = null): void
{
    if (null === $access_time && null === $time) {
        $fun = static fn(): bool => touch($filename);
    } elseif (null === $access_time) {
        $fun = static fn(): bool => touch($filename, $time);
    } else {
        $time = $time ?? $access_time;

        $fun = static fn(): bool => touch($filename, $time, Math\maxva($access_time, $time));
    }

    namespace\create_directory_for_file($filename);

    [$result, $error_message] = Internal\box($fun);
    // @codeCoverageIgnoreStart
    if (false === $result && !namespace\is_file($filename)) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to create file "%s": %s.',
            $filename,
            $error_message ?? 'internal error'
        ));
    }
    // @codeCoverageIgnoreEnd
}
