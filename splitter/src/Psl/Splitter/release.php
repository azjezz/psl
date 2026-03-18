<?php

declare(strict_types=1);

namespace Psl\Splitter;

use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\IO;
use Psl\Shell;
use RuntimeException;

/**
 * Full release flow: split to the correct branch, tag all repos,
 * and create a maintenance branch if this is a x.y.0 release.
 *
 * @param non-empty-string $releaseTag The tag name (e.g. "6.0.0")
 *
 * @throws Shell\Exception\FailedExecutionException If a git operation fails.
 * @throws RuntimeException If a branch is not found on a remote.
 */
function release(MonolithicRepository $monorepo, Git $git, string $releaseTag): void
{
    $branch = MonolithicRepository::branchForTag($releaseTag);

    // Step 0: for patch releases, ensure the maintenance branch is in sync with the tag
    if ($branch !== 'next') {
        IO\write_error_line('');
        Log\info(
            'Syncing maintenance branch %s to tag %s...',
            Log\styled($branch, Ansi\foreground(Color\bright_white()), Style\bold()),
            Log\styled($releaseTag, Ansi\foreground(Color\bright_white()), Style\bold()),
        );
        IO\write_error_line('');

        $git->syncBranch($branch, $releaseTag);
        Log\success('Branch %s synced to %s.', $branch, $releaseTag);
    }

    // Step 1: sync all packages to the source branch
    IO\write_error_line('');
    Log\info('Syncing packages to branch %s...', Log\styled(
        $branch,
        Ansi\foreground(Color\bright_white()),
        Style\bold(),
    ));
    IO\write_error_line('');

    split($monorepo, $git, $branch);

    IO\write_error_line('');
    Log\success('Synced %d packages to %s.', count($monorepo->packages), $branch);

    // Step 2: tag all split repos
    IO\write_error_line('');
    Log\info(
        'Tagging %s on branch %s...',
        Log\styled($releaseTag, Ansi\foreground(Color\bright_white()), Style\bold()),
        Log\styled($branch, Ansi\foreground(Color\bright_white()), Style\bold()),
    );
    IO\write_error_line('');

    tag($monorepo, $git, $releaseTag, $branch);

    IO\write_error_line('');
    Log\success('Tagged %d packages with %s.', count($monorepo->packages), $releaseTag);

    // Step 3: create maintenance branch if x.y.0
    if (MonolithicRepository::isNewReleaseBranch($releaseTag)) {
        IO\write_error_line('');
        Log\info('Creating maintenance branch...');
        IO\write_error_line('');

        create_maintenance_branch($monorepo, $git, $releaseTag);

        IO\write_error_line('');
        Log\success('Maintenance branch created and synced.');
    }
}
