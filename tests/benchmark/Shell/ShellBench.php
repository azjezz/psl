<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Shell;

use PhpBench\Attributes\Groups;
use PhpBench\Attributes\ParamProviders;

use function pack;
use function Psl\Shell\stream_unpack;
use function str_repeat;

#[Groups(['shell'])]
final class ShellBench
{
    /**
     * Benchmark stream_unpack parsing (exercises Str\Byte\length/slice in hot loop).
     *
     * @param array{content: string} $params
     */
    #[ParamProviders('providePackedData')]
    public function benchStreamUnpack(array $params): void
    {
        foreach (stream_unpack($params['content']) as $_type => $_chunk) {
            // @mago-expect lint:no-empty-loop - consume generator
        }
    }

    /**
     * @return iterable<string, array{content: string}>
     */
    public function providePackedData(): iterable
    {
        // Few small chunks
        $content = '';
        for ($i = 0; $i < 10; $i++) {
            $chunk = "chunk_{$i}";
            $content .= pack('C1N1', ($i % 2) + 1, strlen($chunk)) . $chunk;
        }

        yield 'few_small' => ['content' => $content];

        // Many small chunks
        $content = '';
        for ($i = 0; $i < 100; $i++) {
            $chunk = "chunk_{$i}";
            $content .= pack('C1N1', ($i % 2) + 1, strlen($chunk)) . $chunk;
        }

        yield 'many_small' => ['content' => $content];

        // Few large chunks
        $content = '';
        for ($i = 0; $i < 5; $i++) {
            $chunk = str_repeat('x', 10_000);
            $content .= pack('C1N1', ($i % 2) + 1, strlen($chunk)) . $chunk;
        }

        yield 'few_large' => ['content' => $content];

        // Many large chunks
        $content = '';
        for ($i = 0; $i < 50; $i++) {
            $chunk = str_repeat('x', 10_000);
            $content .= pack('C1N1', ($i % 2) + 1, strlen($chunk)) . $chunk;
        }

        yield 'many_large' => ['content' => $content];
    }
}
