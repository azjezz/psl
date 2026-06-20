<?php

declare(strict_types=1);

namespace Psl\Splitter;

use Psl\Async;
use Psl\File;
use Psl\Filesystem;
use Psl\Json;
use Psl\Shell;
use Psl\Str;
use Psl\Type;

/**
 * Prepare the monorepo for the next release by updating branch aliases.
 *
 * Updates `extra.branch-alias.dev-next` in all composer.json files
 * (root + packages) to point to the new version.
 *
 * @param non-empty-string $version The target version in "x.y" format (e.g. "6.1")
 *
 * @throws File\Exception\ExceptionInterface If a file cannot be read or written.
 * @throws Json\Exception\DecodeException If a composer.json file contains invalid JSON.
 */
function prepare(MonolithicRepository $monorepo, string $version): void
{
    $alias = Str\format('dev-next');
    $target = Str\format('%s.x-dev', $version);

    $files = [
        $monorepo->rootPath . '/composer.json',
    ];

    foreach ($monorepo->packages as $package) {
        $files[] = $package->path . '/composer.json';
    }

    foreach ($files as $file) {
        if (!Filesystem\is_file($file)) {
            continue;
        }

        $content = File\read($file);
        $composer = Json\typed::<array>($content, Type\shape::<string, array>([
            'extra' => Type\optional::<array>(Type\dict::<string, mixed>(Type\non_empty_string(), Type\mixed())),
        ], allowUnknownFields: true));

        $composer['extra'] ??= [];
        $composer['extra']['branch-alias'] = [$alias => $target];

        $encoded = Json\encode($composer, true);
        File\write($file, $encoded . "\n", File\WriteMode::Truncate);
    }

    $semaphore = new Async\Semaphore::<string, void>(
        10,
        /** @param non-empty-string $file */
        static function (string $file) use ($alias, $target): void {
            if (!Filesystem\is_file($file)) {
                return;
            }

            $directory = Filesystem\get_directory($file);
            $name = Filesystem\get_basename($directory) . '/composer.json';
            $lockExists = Filesystem\is_file($directory . '/composer.lock');

            Log\step($name, 'updating %s', $lockExists ? 'lock' : 'dependencies');
            Shell\execute('composer', $lockExists ? ['update', '--lock'] : ['update'], $directory);

            Log\step($name, 'normalizing');
            Shell\execute('composer', ['normalize'], $directory);

            Log\step($name, '%s -> %s', $alias, $target);
        },
    );

    $awaitables = [];
    foreach ($files as $file) {
        $awaitables[] = Async\run::<void>(static fn() => $semaphore->waitFor($file));
    }

    Async\all::<int, void>($awaitables);
}
