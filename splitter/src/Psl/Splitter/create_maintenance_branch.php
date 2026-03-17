<?php

declare(strict_types=1);

namespace Psl\Splitter;

use Psl\Shell;
use Psl\Str;

/**
 * Create a maintenance branch (e.g. "6.0.x") from a tag in the monorepo and all split repos.
 *
 * @param non-empty-string $tag The tag to branch from (e.g. "6.0.0")
 *
 * @throws Shell\Exception\FailedExecutionException If a git operation fails.
 */
function create_maintenance_branch(MonolithicRepository $monorepo, Git $git, string $tag): void
{
    $parts = Str\split($tag, '.');
    $maintenanceBranch = $parts[0] . '.' . $parts[1] . '.x';

    // Create and push branch in monorepo
    Log\step('monorepo', 'creating branch %s', $maintenanceBranch);
    $git->createBranch($maintenanceBranch, $tag);
    $git->pushBranch($maintenanceBranch);

    // Split to the new branch in all split repos
    split($monorepo, $git, $maintenanceBranch);
}
