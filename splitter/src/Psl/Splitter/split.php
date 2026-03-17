<?php

declare(strict_types=1);

namespace Psl\Splitter;

use Psl\Async;
use Psl\Async\Exception\CompositeException;
use Psl\Shell;

/**
 * Split all packages to a branch in their respective split repos.
 *
 * Subtree splits run sequentially, pushes run with limited concurrency.
 *
 * @param non-empty-string $branch The branch to push to (e.g. "next", "6.0.x")
 *
 * @throws Shell\Exception\FailedExecutionException If a git operation fails.
 * @throws CompositeException If pushing failed.
 */
function split(MonolithicRepository $monorepo, Git $git, string $branch): void
{
    $splits = [];
    foreach ($monorepo->packages as $package) {
        $prefix = 'packages/' . $package->directory;
        $splitBranch = 'split/' . $package->directory;

        Log\step($package->name, 'subtree split');
        $git->subtreeSplit($prefix, $splitBranch);

        $splits[] = [$package, $splitBranch];
    }

    Log\info('Pushing %d packages (10 concurrent)...', count($splits));

    $semaphore = new Async\Semaphore(
        10,
        /** @param array{Package, non-empty-string} $entry  */
        static function (array $entry) use ($git, $branch): void {
            [$package, $splitBranch] = $entry;

            $git->push($package->repositoryUrl(), $splitBranch, $branch);

            Log\success('%s -> %s', $package->name, $branch);
        },
    );

    $awaitables = [];
    foreach ($splits as $entry) {
        $awaitables[] = Async\run(static fn() => $semaphore->waitFor($entry));
    }

    Async\all($awaitables);
}
