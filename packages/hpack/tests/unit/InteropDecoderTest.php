<?php

declare(strict_types=1);

namespace Psl\HPACK\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\File;
use Psl\Filesystem;
use Psl\HPACK\Decoder;
use Psl\Json;
use Psl\Type;

use function array_keys;
use function array_map;
use function array_values;
use function hex2bin;
use function str_ends_with;

final class InteropDecoderTest extends TestCase
{
    private const string FIXTURE_DIR = __DIR__ . '/../fixture/hpack-test-case';

    private const array IMPLEMENTATIONS = [
        'go-hpack',
        'haskell-http2-linear',
        'haskell-http2-linear-huffman',
        'haskell-http2-naive',
        'haskell-http2-naive-huffman',
        'haskell-http2-static',
        'haskell-http2-static-huffman',
        'nghttp2',
        'nghttp2-16384-4096',
        'nghttp2-change-table-size',
        'node-http2-hpack',
        'python-hpack',
        'swift-nio-hpack-huffman',
        'swift-nio-hpack-plain-text',
    ];

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideStories(): iterable
    {
        foreach (self::IMPLEMENTATIONS as $impl) {
            $dir = self::FIXTURE_DIR . '/' . $impl;

            foreach (Filesystem\read_directory($dir) as $entry) {
                if (!str_ends_with($entry, '.json')) {
                    continue;
                }

                $storyName = Filesystem\get_basename($entry, '.json');

                yield $impl . '/' . $storyName => [$impl, $entry];
            }
        }
    }

    /**
     * @param non-empty-string $file
     */
    #[DataProvider('provideStories')]
    public function testDecodeStory(string $impl, string $file): void
    {
        $story = Json\typed(File\read($file), self::storyType());

        $decoder = new Decoder(4_096, 1_000_000);

        foreach ($story['cases'] as $case) {
            if (isset($case['header_table_size'])) {
                $decoder->resize($case['header_table_size']);
            }

            $wire = hex2bin($case['wire']);
            $decoded = $decoder->decode($wire);

            $expected = array_map(static fn(array $h): array => [
                array_keys($h)[0],
                array_values($h)[0],
            ], $case['headers']);

            $actual = array_map(static fn($h): array => [$h->name, $h->value], $decoded);

            static::assertSame($expected, $actual, $impl . ' seqno=' . ($case['seqno'] ?? '?'));
        }
    }

    /**
     * @return Type\TypeInterface<array{
     *     cases: list<array{
     *         seqno?: int,
     *         header_table_size?: null|non-negative-int,
     *         wire: string,
     *         headers: list<array<string, string>>,
     *     }>,
     * }>
     */
    private static function storyType(): Type\TypeInterface
    {
        return Type\shape([
            'cases' => Type\vec(Type\shape([
                'seqno' => Type\optional(Type\int()),
                'header_table_size' => Type\optional(Type\nullable(Type\uint())),
                'wire' => Type\string(),
                'headers' => Type\vec(Type\dict(Type\string(), Type\string())),
            ])),
        ]);
    }
}
