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
            $repo = Str\after($package->name, '/') ?? $package->name;
            $org = Str\before($package->name, '/') ?? 'php-standard-library';
            $apiBase = Str\format('https://api.github.com/repos/%s/%s', $org, $repo);

            $tagResponse = Http\post(
                $apiBase . '/git/tags',
                Json\encode([
                    'tag' => $tag,
                    'message' => $tag,
                    'object' => $sha,
                    'type' => 'commit',
                ]),
                $headers,
            );

            if (!$tagResponse->isOk()) {
                $body = Json\typed($tagResponse->body, Type\shape([
                    'message' => Type\string(),
                ], allowUnknownFields: true));

                Log\error('%s tag object failed: %s', $package->name, $body['message']);
                return;
            }

            $tagSha = Json\typed($tagResponse->body, Type\shape([
                'sha' => Type\non_empty_string(),
            ], allowUnknownFields: true))['sha'];

            $refResponse = Http\post(
                $apiBase . '/git/refs',
                Json\encode([
                    'ref' => 'refs/tags/' . $tag,
                    'sha' => $tagSha,
                ]),
                $headers,
            );

            if (!$refResponse->isOk()) {
                $body = Json\typed($refResponse->body, Type\shape([
                    'message' => Type\string(),
                ], allowUnknownFields: true));

                Log\error('%s tag ref failed: %s', $package->name, $body['message']);
                return;
            }
        } else {
            Log\warn('github token not set, falling back to `git push`');
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
