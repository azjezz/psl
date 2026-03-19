<?php

declare(strict_types=1);

namespace Psl\Compression;

/**
 * Decompress a string in one shot.
 *
 * @throws Exception\RuntimeException If the decompression operation fails.
 */
function decompress(string $data, DecompressorInterface $decompressor): string
{
    return $decompressor->push($data) . $decompressor->finish();
}
