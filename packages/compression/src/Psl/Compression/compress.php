<?php

declare(strict_types=1);

namespace Psl\Compression;

/**
 * Compress a string in one shot.
 *
 * @throws Exception\RuntimeException If the compression operation fails.
 */
function compress(string $data, CompressorInterface $compressor): string
{
    return $compressor->push($data) . $compressor->finish();
}
