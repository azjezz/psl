<?php

declare(strict_types=1);

namespace Psl\Splitter;

use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\Async\Exception\CompositeException;
use Psl\IO;
use Psl\Iter;
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
 * @throws CompositeException If splitting, or creating releases fails.
 */
function release(MonolithicRepository $monorepo, Git $git, string $releaseTag): void
{
    $branch = MonolithicRepository::branchForTag($releaseTag);

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

    IO\write_error_line('');
    Log\info('Syncing packages to branch %s...', Log\styled(
        $branch,
        Ansi\foreground(Color\bright_white()),
        Style\bold(),
    ));
    IO\write_error_line('');

    namespace\split($monorepo, $git, $branch);

    IO\write_error_line('');
    Log\success('Synced %d packages to %s.', Iter\count($monorepo->packages), $branch);

    IO\write_error_line('');
    Log\info(
        'Tagging %s on branch %s...',
        Log\styled($releaseTag, Ansi\foreground(Color\bright_white()), Style\bold()),
        Log\styled($branch, Ansi\foreground(Color\bright_white()), Style\bold()),
    );
    IO\write_error_line('');

    namespace\tag($monorepo, $git, $releaseTag, $branch);

    IO\write_error_line('');
    Log\success('Tagged %d packages with %s.', Iter\count($monorepo->packages), $releaseTag);

    IO\write_error_line('');
    Log\info('Creating releases for %s...', Log\styled(
        $releaseTag,
        Ansi\foreground(Color\bright_white()),
        Style\bold(),
    ));
    IO\write_error_line('');

    namespace\create_releases($monorepo, $releaseTag);

    IO\write_error_line('');
    Log\success('Created releases for %s.', $releaseTag);

    if (MonolithicRepository::isNewReleaseBranch($releaseTag)) {
        IO\write_error_line('');
        Log\info('Creating maintenance branch...');
        IO\write_error_line('');

        namespace\create_maintenance_branch($monorepo, $git, $releaseTag);

        IO\write_error_line('');
        Log\success('Maintenance branch created and synced.');
    }
}
