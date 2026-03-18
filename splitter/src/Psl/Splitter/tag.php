<?php

declare(strict_types=1);

namespace Psl\Splitter;

use Psl\Async;
use Psl\Env;
use Psl\Json;
use Psl\Str;
use Psl\Type;

/**
 * Tag all split repos at the HEAD of a branch using the GitHub API.
 *
 * Creates tags via the API rather than raw git push, so GitHub marks them as "verified".
 *
 * @param non-empty-string $tag The tag name (e.g. "6.0.0")
 * @param non-empty-string $branch The branch whose HEAD to tag (e.g. "next", "6.0.x")
 */
function tag(MonolithicRepository $monorepo, Git $git, string $tag, string $branch): void
{
    $token = Env\get_var('GITHUB_TOKEN') ?? '';

    $headers = [
        'authorization' => 'Bearer ' . $token,
        'accept' => 'application/vnd.github+json',
        'x-github-api-version' => '2022-11-28',
        'content-type' => 'application/json',
    ];

    $semaphore = new Async\Semaphore(10, static function (Package $package) use (
        $git,
        $tag,
        $branch,
        $token,
        $headers,
    ): void {
        $repoUrl = $package->repositoryUrl();

        // Get the SHA of the branch HEAD
        $lsRemote = Str\trim($git->lsRemote($repoUrl, 'refs/heads/' . $branch));
        if ($lsRemote === '') {
            Log\warn('%s branch %s not found, skipping', $package->name, $branch);
            return;
        }

        $sha = Str\before($lsRemote, "\t") ?? $lsRemote;

        if ($token !== '') {
            // Use GitHub API for verified tags
            $repo = Str\after($package->name, '/') ?? $package->name;
            $org = Str\before($package->name, '/') ?? 'php-standard-library';

            $response = Http\post(
                Str\format('https://api.github.com/repos/%s/%s/git/refs', $org, $repo),
                Json\encode([
                    'ref' => 'refs/tags/' . $tag,
                    'sha' => $sha,
                ]),
                $headers,
            );

            if (!$response->isOk()) {
                $body = Json\typed($response->body, Type\shape([
                    'message' => Type\string(),
                ], allowUnknownFields: true));

                Log\error('%s tag failed: %s', $package->name, $body['message']);
                return;
            }
        } else {
            // Fallback to git push when no token available
            $git->tagRemote($repoUrl, $tag, $branch);
        }

        Log\success('%s tag %s', $package->name, $tag);
    });

    $awaitables = [];
    foreach ($monorepo->packages as $package) {
        $awaitables[] = Async\run(static fn() => $semaphore->waitFor($package));
    }

    Async\all($awaitables);
}
