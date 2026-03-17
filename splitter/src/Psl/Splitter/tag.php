<?php

declare(strict_types=1);

namespace Psl\Splitter;

use Psl\Shell;
use RuntimeException;

/**
 * Tag all split repos at the HEAD of a branch.
 *
 * @param non-empty-string $tag The tag name (e.g. "6.0.0")
 * @param non-empty-string $branch The branch whose HEAD to tag (e.g. "next", "6.0.x")
 *
 * @throws Shell\Exception\FailedExecutionException If a git operation fails.
 * @throws RuntimeException If a branch is not found on a remote.
 */
function tag(MonolithicRepository $monorepo, Git $git, string $tag, string $branch): void
{
    foreach ($monorepo->packages as $package) {
        Log\step($package->name, 'tag %s', $tag);
        $git->tagRemote($package->repositoryUrl(), $tag, $branch);
    }
}
