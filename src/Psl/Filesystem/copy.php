<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\File;
use Psl\IO;
use Psl\Str;

/**
 * Copy a file from $source to $destination and preserve executable permission bits.
 *
 * @param non-empty-string $source
 * @param non-empty-string $destination
 *
 * @throws Exception\RuntimeException If unable to copy $source to $destination.
 * @throws Exception\NotFoundException If $source is not found.
 * @throws Exception\NotReadableException If $source is not readable.
 */
function copy(string $source, string $destination, bool $overwrite = false): void
{
    $destination_exists = namespace\is_file($destination);
    if (!$overwrite && $destination_exists) {
        return;
    }

    if (!namespace\is_file($source)) {
        throw Exception\NotFoundException::forFile($source);
    }

    if (!namespace\is_readable($source)) {
        throw Exception\NotReadableException::forFile($source);
    }

    $source_lock = null;
    $destination_lock = null;
    try {
        $source_handle = File\open_read_only($source);
        $destination_handle = File\open_write_only(
            $destination,
            $destination_exists ? File\WriteMode::TRUNCATE : File\WriteMode::OPEN_OR_CREATE,
        );

        $source_lock = $source_handle->lock(File\LockType::SHARED);
        $destination_lock = $destination_handle->lock(File\LockType::EXCLUSIVE);

        while ($chunk = $source_handle->read()) {
            $destination_handle->writeAll($chunk);

            // free memory
            unset($chunk);
        }
        // @codeCoverageIgnoreStart
    } catch (IO\Exception\ExceptionInterface | File\Exception\ExceptionInterface | Psl\Exception\InvariantViolationException $exception) {
        throw new Exception\RuntimeException(Str\format('Failed to copy source file "%s" to destination "%s".', $source, $destination), previous: $exception);
    } finally {
        // @codeCoverageIgnoreEnd
        $source_lock?->release();
        $destination_lock?->release();
    }

    // preserve executable permission bits
    change_permissions(
        $destination,
        get_permissions($destination) | (get_permissions($source) & 0111)
    );
}
