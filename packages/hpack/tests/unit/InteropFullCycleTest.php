<?php

declare(strict_types=1);

namespace Psl\HPACK\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\File;
use Psl\Filesystem;
use Psl\HPACK\Decoder;
use Psl\HPACK\Encoder;
use Psl\HPACK\Header;
use Psl\Json;
use Psl\Type;

use function array_map;
use function hex2bin;
use function str_ends_with;

/**
 * Full cycle test: decode each implementation's wire format, re-encode, re-decode,
 * and verify the headers match the original.
 */
final class InteropFullCycleTest extends TestCase
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
    public function testFullCycle(string $impl, string $file): void
    {
        $story = Json\typed::<array>(File\read($file), self::storyType());

        $decoder1 = new Decoder(4_096, 1_000_000);
        $encoder = new Encoder();
        $decoder2 = new Decoder(4_096, 1_000_000);

        foreach ($story['cases'] as $case) {
            if (isset($case['header_table_size'])) {
                $decoder1->resize($case['header_table_size']);
                $encoder->resize($case['header_table_size']);
                $decoder2->resize($case['header_table_size']);
            }

            $wire = hex2bin($case['wire']);
            $decoded = $decoder1->decode($wire);

            $reEncoded = $encoder->encode($decoded);

            $reDecoded = $decoder2->decode($reEncoded);

            $expected = array_map(static fn(Header $h): array => [$h->name, $h->value], $decoded);
            $actual = array_map(static fn(Header $h): array => [$h->name, $h->value], $reDecoded);

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
    private static function storyType(): Type\TypeInterface<array>
    {
        return Type\shape::<string, array>([
            'cases' => Type\vec::<array>(Type\shape::<string, mixed>([
                'seqno' => Type\optional::<int>(Type\int()),
                'header_table_size' => Type\nullish::<int>(Type\uint()),
                'wire' => Type\string(),
                'headers' => Type\vec::<array>(Type\dict::<string, string>(Type\string(), Type\string())),
            ])),
        ]);
    }
}
