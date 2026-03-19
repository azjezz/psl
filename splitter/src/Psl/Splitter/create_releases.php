<?php

declare(strict_types=1);

namespace Psl\Splitter;

use Psl\Async;
use Psl\Async\Exception\CompositeException;
use Psl\Env;
use Psl\Json;
use Psl\Str;
use Psl\Type;

/**
 * Create GitHub releases for all split repos and the main repo.
 *
 * Sub-package releases point back to the main repo release.
 * The main repo release is created with an empty body for manual editing.
 *
 * @param non-empty-string $tag The tag name (e.g. "6.0.0")
 *
 * @throws CompositeException If creating releases fails.
 */
function create_releases(MonolithicRepository $monorepo, string $tag): void
{
    $token = Env\get_var('GITHUB_TOKEN') ?? '';
    if ($token === '') {
        Log\warn('GITHUB_TOKEN not set, skipping release creation');
        return;
    }

    $org = 'php-standard-library';
    $headers = [
        'authorization' => 'Bearer ' . $token,
        'accept' => 'application/vnd.github+json',
        'x-github-api-version' => '2022-11-28',
        'content-type' => 'application/json',
    ];

    $errorType = Type\shape([
        'message' => Type\string(),
    ], allowUnknownFields: true);

    $semaphore = new Async\Semaphore(10, static function (Package $package) use (
        $org,
        $tag,
        $headers,
        $errorType,
    ): void {
        $repo = Str\after($package->name, '/') ?? $package->name;
        $apiBase = Str\format('https://api.github.com/repos/%s/%s', $org, $repo);
        $mainRelease = Str\format('https://github.com/%s/%s/releases/tag/%s', $org, $org, $tag);

        $response = Http\post(
            $apiBase . '/releases',
            Json\encode([
                'tag_name' => $tag,
                'name' => $tag,
                'body' => Str\format('See the [main repository release](%s) for details.', $mainRelease),
                'draft' => false,
                'prerelease' => false,
            ]),
            $headers,
        );

        if (!$response->isOk()) {
            $body = Json\typed($response->body, $errorType);
            Log\error('%s release failed: %s', $package->name, $body['message']);
            return;
        }

        Log\success('%s release %s created', $package->name, $tag);
    });

    $awaitables = [];
    foreach ($monorepo->packages as $package) {
        $awaitables[] = Async\run(static fn() => $semaphore->waitFor($package));
    }

    Async\all($awaitables);

    // Create release for the main repo
    $response = Http\post(
        Str\format('https://api.github.com/repos/%s/%s/releases', $org, $org),
        Json\encode([
            'tag_name' => $tag,
            'name' => $tag,
            'body' => '',
            'draft' => false,
            'prerelease' => false,
        ]),
        $headers,
    );

    if (!$response->isOk()) {
        $body = Json\typed($response->body, $errorType);
        Log\error('main repo release failed: %s', $body['message']);
        return;
    }

    Log\success('main repo release %s created', $tag);
}
