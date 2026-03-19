<?php

declare(strict_types=1);

namespace Psl\Compression\Tests\Fixture;

use Psl\Compression;
use Psl\Compression\Exception;

use function brotli_uncompress_add;
use function brotli_uncompress_init;

use const BROTLI_FINISH;
use const BROTLI_PROCESS;

final class BrotliDecompressor implements Compression\DecompressorInterface
{
    /**
     * @var resource
     */
    private mixed $context;

    public function __construct()
    {
        $this->context = brotli_uncompress_init();
    }

    public function push(string $data): string
    {
        $result = brotli_uncompress_add($this->context, $data, BROTLI_PROCESS);
        if (false === $result) {
            throw new Exception\RuntimeException('Brotli decompression failed.');
        }

        return $result;
    }

    public function finish(): string
    {
        $result = brotli_uncompress_add($this->context, '', BROTLI_FINISH);
        if (false === $result) {
            throw new Exception\RuntimeException('Brotli decompression finalization failed.');
        }

        $this->context = brotli_uncompress_init();

        return $result;
    }
}
