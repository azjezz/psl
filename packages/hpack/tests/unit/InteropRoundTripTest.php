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

use function array_keys;
use function array_map;
use function array_values;
use function str_ends_with;

final class InteropRoundTripTest extends TestCase
{
    private const string RAW_DATA_DIR = __DIR__ . '/../fixture/hpack-test-case/raw-data';

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideRawStories(): iterable
    {
        foreach (Filesystem\read_directory(self::RAW_DATA_DIR) as $entry) {
            if (!str_ends_with($entry, '.json')) {
                continue;
            }

            $storyName = Filesystem\get_basename($entry, '.json');

            yield $storyName => [$entry];
        }
    }

    /**
     * @param non-empty-string $file
     */
    #[DataProvider('provideRawStories')]
    public function testEncodeDecodeRoundTrip(string $file): void
    {
        $story = Json\typed(File\read($file), self::storyType());

        $encoder = new Encoder();
        $decoder = new Decoder(4_096, 1_000_000);

        foreach ($story['cases'] as $case) {
            $headers = array_map(
                static fn(array $h): Header => new Header(array_keys($h)[0], array_values($h)[0]),
                $case['headers'],
            );

            $expected = array_map(static fn(Header $h): array => [$h->name, $h->value], $headers);

            $decoded = $decoder->decode($encoder->encode($headers));

            $actual = array_map(static fn(Header $h): array => [$h->name, $h->value], $decoded);

            static::assertSame($expected, $actual);
        }
    }

    /**
     * @param non-empty-string $file
     */
    #[DataProvider('provideRawStories')]
    public function testEncodeDecodeWithSmallTable(string $file): void
    {
        $story = Json\typed(File\read($file), self::storyType());

        $encoder = new Encoder(256);
        $decoder = new Decoder(256, 1_000_000);

        foreach ($story['cases'] as $case) {
            $headers = array_map(
                static fn(array $h): Header => new Header(array_keys($h)[0], array_values($h)[0]),
                $case['headers'],
            );

            $expected = array_map(static fn(Header $h): array => [$h->name, $h->value], $headers);

            $decoded = $decoder->decode($encoder->encode($headers));

            $actual = array_map(static fn(Header $h): array => [$h->name, $h->value], $decoded);

            static::assertSame($expected, $actual);
        }
    }

    /**
     * @return Type\TypeInterface<array{
     *     cases: list<array{
     *         headers: list<array<non-empty-string, string>>,
     *     }>,
     * }>
     */
    private static function storyType(): Type\TypeInterface
    {
        return Type\shape([
            'cases' => Type\vec(Type\shape([
                'headers' => Type\vec(Type\dict(Type\non_empty_string(), Type\string())),
            ])),
        ]);
    }
}
