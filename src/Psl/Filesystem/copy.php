<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\Internal;
use Psl\Str;

use function fclose;
use function fopen;
use function stream_copy_to_stream;

/**
 * Change the group ownership of $filename.
 *
 * @throws Exception\RuntimeException If unable to change the group ownership for $filename.
 * @throws Psl\Exception\InvariantViolationException If $source does not exist or is not readable.
 */
function copy(string $source, string $destination, bool $overwrite = false): void
{
    Psl\invariant(is_file($source) && is_readable($source), 'Source "%s" does not exist or is not readable.', $source);

    if (!$overwrite && is_file($destination)) {
        return;
    }

    /**
     * @psalm-suppress InvalidArgument - callable is not pure..
     */
    $source_stream = Internal\suppress(static fn() => fopen($source, 'rb'));
    // @codeCoverageIgnoreStart
    if (false === $source_stream) {
        throw new Exception\RuntimeException('Failed to open $source file for reading');
    }
    // @codeCoverageIgnoreEnd

    /**
     * @psalm-suppress InvalidArgument - callable is not pure..
     */
    $destination_stream = Internal\suppress(static fn() => fopen($destination, 'wb'));
    // @codeCoverageIgnoreStart
    if (false === $destination_stream) {
        throw new Exception\RuntimeException('Failed to open $destination file for writing.');
    }
    // @codeCoverageIgnoreEnd

    $copied_bytes = stream_copy_to_stream($source_stream, $destination_stream);
    fclose($source_stream);
    fclose($destination_stream);

    $total_bytes = file_size($source);

    // preserve executable permission bits
    change_permissions(
        $destination,
        get_permissions($destination) | (get_permissions($source) & 0111)
    );

    // @codeCoverageIgnoreStart
    if ($copied_bytes !== $total_bytes) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to copy the whole content of "%s" to "%s" ( %g of %g bytes copied ).',
            $source,
            $destination,
            $copied_bytes,
            $total_bytes
        ));
    }
    // @codeCoverageIgnoreEnd
}
