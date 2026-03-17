<?php

declare(strict_types=1);

namespace Psl\Splitter;

use Psl\Shell;

/**
 * Split all packages to a branch in their respective split repos.
 *
 * @param non-empty-string $branch The branch to push to (e.g. "next", "6.0.x")
 *
 * @throws Shell\Exception\FailedExecutionException If a git operation fails.
 */
function split(MonolithicRepository $monorepo, Git $git, string $branch): void
{
    foreach ($monorepo->packages as $package) {
        $prefix = 'packages/' . $package->directory;
        $splitBranch = 'split/' . $package->directory;

        Log\step($package->name, 'subtree split');
        $git->subtreeSplit($prefix, $splitBranch);

        Log\step($package->name, 'push -> %s', $branch);
        $git->push($package->repositoryUrl(), $splitBranch, $branch);
    }
}
