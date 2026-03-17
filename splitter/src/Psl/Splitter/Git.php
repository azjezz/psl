<?php

declare(strict_types=1);

namespace Psl\Splitter;

use Psl\Shell;
use Psl\Str;
use Psl\Type;
use Psl\Vec;
use RuntimeException;

/**
 * Git operations for splitting and pushing packages.
 */
final readonly class Git
{
    /**
     * @param non-empty-string $workingDirectory
     */
    public function __construct(
        private string $workingDirectory,
    ) {}

    /**
     * Split a subtree and return the resulting commit hash.
     *
     * @param non-empty-string $prefix e.g. "packages/type"
     * @param non-empty-string $branch e.g. "split/type"
     *
     * @throws Shell\Exception\FailedExecutionException If the git command fails.
     *
     * @return non-empty-string The commit SHA of the split
     */
    public function subtreeSplit(string $prefix, string $branch): string
    {
        $sha = $this->run('subtree', 'split', '--prefix=' . $prefix, '--branch=' . $branch);

        return Type\non_empty_string()->assert(Str\trim($sha));
    }

    /**
     * Push a split branch to a remote repository.
     *
     * @param non-empty-string $repoUrl
     * @param non-empty-string $localBranch
     * @param non-empty-string $remoteBranch
     *
     * @throws Shell\Exception\FailedExecutionException If the git push fails.
     */
    public function push(string $repoUrl, string $localBranch, string $remoteBranch, bool $force = false): void
    {
        $args = ['push', $repoUrl, $localBranch . ':refs/heads/' . $remoteBranch];
        if ($force) {
            $args[] = '--force';
        }

        $this->run(...$args);
    }

    /**
     * Tag a remote repository at the current HEAD of a branch.
     *
     * @param non-empty-string $repoUrl
     * @param non-empty-string $tag
     * @param non-empty-string $branch
     *
     * @throws Shell\Exception\FailedExecutionException If the git command fails.
     * @throws RuntimeException If the branch is not found on the remote.
     */
    public function tagRemote(string $repoUrl, string $tag, string $branch): void
    {
        $sha = Str\trim($this->run('ls-remote', $repoUrl, 'refs/heads/' . $branch));
        if ($sha === '') {
            throw new RuntimeException(Str\format('Branch "%s" not found on remote "%s"', $branch, $repoUrl));
        }

        // Extract just the SHA (ls-remote returns "sha\trefs/heads/branch")
        $sha = Str\before($sha, "\t") ?? $sha;

        $this->run('push', $repoUrl, $sha . ':refs/tags/' . $tag);
    }

    /**
     * Create a local branch from a tag.
     *
     * @param non-empty-string $branch
     * @param non-empty-string $tag
     *
     * @throws Shell\Exception\FailedExecutionException If the git command fails.
     */
    public function createBranch(string $branch, string $tag): void
    {
        $this->run('branch', $branch, $tag);
    }

    /**
     * Push a local branch to origin.
     *
     * @param non-empty-string $branch
     *
     * @throws Shell\Exception\FailedExecutionException If the git push fails.
     */
    public function pushBranch(string $branch): void
    {
        $this->run('push', 'origin', $branch);
    }

    /**
     * Truncate history at the first commit that introduced the given path.
     *
     * Uses `git replace --graft` to make that commit a root, so subsequent
     * operations (like subtree split) don't walk irrelevant history.
     *
     * @param non-empty-string $path e.g. "packages/"
     *
     * @throws Shell\Exception\FailedExecutionException If a git command fails.
     */
    public function truncateHistoryAt(string $path): void
    {
        $base = Str\trim($this->run('log', '--reverse', '--format=%H', '--', $path));
        $first = Str\before($base, "\n") ?? $base;

        if ($first === '') {
            return;
        }

        Log\info('Truncating history at %s (first commit with %s)', Str\slice($first, 0, 12), $path);

        $this->run('replace', '--graft', $first);
    }

    /**
     * Run a git command and return stdout.
     *
     * @throws Shell\Exception\FailedExecutionException If the command exits with a non-zero code.
     */
    private function run(string ...$args): string
    {
        $args = Vec\values($args);

        Log\command('git %s', Str\join($args, ' '));

        return Shell\execute('git', $args, $this->workingDirectory);
    }
}
