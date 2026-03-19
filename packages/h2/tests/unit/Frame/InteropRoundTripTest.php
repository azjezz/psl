<?php

declare(strict_types=1);

namespace Psl\H2\Tests\Unit\Frame;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\File;
use Psl\Filesystem;
use Psl\H2\Frame;
use Psl\Json;
use Psl\Type;

use function hex2bin;
use function str_ends_with;

final class InteropRoundTripTest extends TestCase
{
    private const string FIXTURE_DIR = __DIR__ . '/../../fixture/http2-frame-test-case';

    private const array FRAME_DIRS = [
        'data',
        'headers',
        'priority',
        'rst_stream',
        'settings',
        'push_promise',
        'ping',
        'goaway',
        'window_update',
        'continuation',
    ];

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideFixtures(): iterable
    {
        foreach (self::FRAME_DIRS as $dir) {
            $path = self::FIXTURE_DIR . '/' . $dir;
            if (!Filesystem\is_directory($path)) {
                continue;
            }

            foreach (Filesystem\read_directory($path) as $entry) {
                if (!str_ends_with($entry, '.json')) {
                    continue;
                }

                $name = $dir . '/' . Filesystem\get_basename($entry, '.json');

                yield $name => [$entry];
            }
        }
    }

    /**
     * @param non-empty-string $file
     */
    #[DataProvider('provideFixtures')]
    public function testRoundTrip(string $file): void
    {
        $fixture = Json\typed(File\read($file), self::fixtureType());

        if ($fixture['error'] !== null) {
            static::markTestSkipped('Error fixture — not a round-trip test');
        }

        $wire = hex2bin($fixture['wire']);
        [$rawFrame] = Frame\decode($wire, 0);

        $reEncoded = Frame\encode($rawFrame);
        [$reDecoded] = Frame\decode($reEncoded, 0);

        static::assertSame($rawFrame->type, $reDecoded->type);
        static::assertSame($rawFrame->flags, $reDecoded->flags);
        static::assertSame($rawFrame->streamId, $reDecoded->streamId);
        static::assertSame($rawFrame->payload, $reDecoded->payload);
    }

    /**
     * @return Type\TypeInterface<array{
     *     error: null|list<int>,
     *     wire: string,
     *     frame: null|array<string, mixed>,
     *     description?: string,
     * }>
     */
    private static function fixtureType(): Type\TypeInterface
    {
        return Type\shape([
            'error' => Type\nullable(Type\vec(Type\int())),
            'wire' => Type\string(),
            'frame' => Type\nullable(Type\dict(Type\string(), Type\mixed())),
            'description' => Type\optional(Type\string()),
        ]);
    }
}
