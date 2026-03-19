<?php

declare(strict_types=1);

namespace Psl\Compression\Tests\Fixture;

use Psl\Compression;
use Psl\Compression\Exception;

use function brotli_compress_add;
use function brotli_compress_init;

use const BROTLI_FINISH;
use const BROTLI_PROCESS;

final class BrotliCompressor implements Compression\CompressorInterface
{
    /**
     * @var resource
     */
    private mixed $context;

    public function __construct()
    {
        $this->context = brotli_compress_init();
    }

    public function push(string $data): string
    {
        $result = brotli_compress_add($this->context, $data, BROTLI_PROCESS);
        if (false === $result) {
            throw new Exception\RuntimeException('Brotli compression failed.');
        }

        return $result;
    }

    public function finish(): string
    {
        $result = brotli_compress_add($this->context, '', BROTLI_FINISH);
        if (false === $result) {
            throw new Exception\RuntimeException('Brotli compression finalization failed.');
        }

        $this->context = brotli_compress_init();

        return $result;
    }
}
