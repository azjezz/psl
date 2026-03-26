<?php

declare(strict_types=1);

namespace Psl\Compression\Tests\Fixture;

use Psl\Compression\DecompressorInterface;

final class NullDecompressor implements DecompressorInterface
{
    public function push(string $data): string
    {
        return $data;
    }

    public function finish(): string
    {
        return '';
    }
}
