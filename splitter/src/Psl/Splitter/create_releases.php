<?php

declare(strict_types=1);

namespace Psl\Splitter;

use Psl\Async;
use Psl\Async\Exception\CompositeException;
use Psl\Env;
use Psl\HTTP\Client;
use Psl\HTTP\Message;
use Psl\IO;
use Psl\Json;
use Psl\Str;
use Psl\Type;
use Psl\URL;

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
function create_releases(Client\Client $httpClient, MonolithicRepository $monorepo, string $tag): void
{
    $token = Env\get_var('GITHUB_TOKEN') ?? '';
    if ($token === '') {
        Log\warn('GITHUB_TOKEN not set, skipping release creation');
        return;
    }

    $org = 'php-standard-library';
    $headers = Message\FieldMap::from([
        ['authorization', 'Bearer ' . $token],
        ['accept', 'application/vnd.github+json'],
        ['x-github-api-version', '2022-11-28'],
        ['content-type', 'application/json'],
    ]);

    $errorType = Type\shape([
        'message' => Type\string(),
    ], allowUnknownFields: true);

    $semaphore = new Async\Semaphore(10, static function (Package $package) use (
        $org,
        $tag,
        $headers,
        $errorType,
        $httpClient,
    ): void {
        $repo = Str\after($package->name, '/') ?? $package->name;
        $mainRelease = Str\format('https://github.com/%s/%s/releases/tag/%s', $org, $org, $tag);

        $transaction = $httpClient->send(new Message\Request(
            method: Message\METHOD_POST,
            url: URL\parse(Str\format('https://api.github.com/repos/%s/%s/releases', $org, $repo)),
            headers: $headers,
            body: new IO\MemoryHandle(Json\encode([
                'tag_name' => $tag,
                'name' => $tag,
                'body' => Str\format('See the [main repository release](%s) for details.', $mainRelease),
                'draft' => false,
                'prerelease' => false,
            ])),
        ));

        if ($transaction->response->status !== Message\STATUS_OK) {
            $content = $transaction->response->body?->readAll() ?? '';
            $body = Json\typed($content, $errorType);
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
    $transaction = $httpClient->send(new Message\Request(
        method: Message\METHOD_POST,
        url: URL\parse(Str\format('https://api.github.com/repos/%s/%s/releases', $org, $org)),
        headers: $headers,
        body: new IO\MemoryHandle(Json\encode([
            'tag_name' => $tag,
            'name' => $tag,
            'body' => '',
            'draft' => false,
            'prerelease' => false,
        ])),
    ));

    if ($transaction->response->status !== Message\STATUS_OK) {
        $content = $transaction->response->body?->readAll() ?? '';
        $body = Json\typed($content, $errorType);
        Log\error('main repo release failed: %s', $body['message']);
        return;
    }

    Log\success('main repo release %s created', $tag);
}
