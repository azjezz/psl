<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Encoding;

use PhpBench\Attributes\Groups;
use PhpBench\Attributes\ParamProviders;
use Psl\Encoding\Base64;
use Psl\Encoding\Hex;

#[Groups(['encoding'])]
final class EncodingBench
{
    /**
     * @param array{data: string} $params
     */
    #[ParamProviders('provideBinaryData')]
    public function benchBase64Encode(array $params): void
    {
        $_ = Base64\encode($params['data']);
    }

    /**
     * @param array{data: string} $params
     */
    #[ParamProviders('provideBase64Data')]
    public function benchBase64Decode(array $params): void
    {
        $_ = Base64\decode($params['data']);
    }

    /**
     * @param array{data: string} $params
     */
    #[ParamProviders('provideBinaryData')]
    public function benchHexEncode(array $params): void
    {
        $_ = Hex\encode($params['data']);
    }

    /**
     * @param array{data: string} $params
     */
    #[ParamProviders('provideHexData')]
    public function benchHexDecode(array $params): void
    {
        $_ = Hex\decode($params['data']);
    }

    /**
     * @return iterable<non-empty-string, array{data: string}>
     */
    public function provideBinaryData(): iterable
    {
        yield 'small (32B)' => ['data' => random_bytes(32)];
        yield 'medium (256B)' => ['data' => random_bytes(256)];
        yield 'large (4KB)' => ['data' => random_bytes(4096)];
    }

    /**
     * @return iterable<non-empty-string, array{data: string}>
     */
    public function provideBase64Data(): iterable
    {
        yield 'small (32B)' => ['data' => base64_encode(random_bytes(32))];
        yield 'medium (256B)' => ['data' => base64_encode(random_bytes(256))];
        yield 'large (4KB)' => ['data' => base64_encode(random_bytes(4096))];
    }

    /**
     * @return iterable<non-empty-string, array{data: string}>
     */
    public function provideHexData(): iterable
    {
        yield 'small (32B)' => ['data' => bin2hex(random_bytes(32))];
        yield 'medium (256B)' => ['data' => bin2hex(random_bytes(256))];
        yield 'large (4KB)' => ['data' => bin2hex(random_bytes(4096))];
    }
}
