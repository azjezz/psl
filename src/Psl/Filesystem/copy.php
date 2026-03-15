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
    $destinationExists = namespace\is_file($destination);
    if (!$overwrite && $destinationExists) {
        return;
    }

    if (!namespace\is_file($source)) {
        throw Exception\NotFoundException::forFile($source);
    }

    if (!namespace\is_readable($source)) {
        throw Exception\NotReadableException::forFile($source);
    }

    $sourceHandle = null;
    $destinationHandle = null;
    $sourceLock = null;
    $destinationLock = null;
    try {
        $sourceHandle = File\open_read_only($source);
        $destinationHandle = File\open_write_only(
            $destination,
            $destinationExists ? File\WriteMode::Truncate : File\WriteMode::OpenOrCreate,
        );

        $sourceLock = $sourceHandle->lock(File\LockType::Shared);
        $destinationLock = $destinationHandle->lock(File\LockType::Exclusive);

        do {
            $chunk = $sourceHandle->read();
            if ('' === $chunk) {
                break;
            }

            $destinationHandle->writeAll($chunk);

            // free memory
            unset($chunk);
        } while (true);
        // @codeCoverageIgnoreStart
    } catch (
        IO\Exception\ExceptionInterface|File\Exception\ExceptionInterface|Psl\Exception\InvariantViolationException $exception
    ) {
        throw new Exception\RuntimeException(
            Str\format('Failed to copy source file "%s" to destination "%s".', $source, $destination),
            previous: $exception,
        );
    } finally {
        // @codeCoverageIgnoreEnd
        $sourceLock?->release();
        $destinationLock?->release();
        $sourceHandle?->close();
        $destinationHandle?->close();
    }

    // preserve executable permission bits
    change_permissions($destination, get_permissions($destination) | (get_permissions($source) & 0o111));
}
