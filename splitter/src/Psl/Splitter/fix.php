<?php

declare(strict_types=1);

namespace Psl\Splitter;

use Psl\Async;
use Psl\Dict;
use Psl\HTTP\Client;
use Psl\HTTP\Message;
use Psl\IO;
use Psl\Iter;
use Psl\Json;
use Psl\Str;
use Psl\Type;
use Psl\URL;
use Psl\Vec;
use SensitiveParameter;

/**
 * Fix all auditable issues across package repositories.
 *
 * For all repositories:
 * - Set description from composer.json
 * - Set homepage to the project website
 * - Set topics from composer.json keywords
 * - Disable wiki
 * - Create tag immutability ruleset if missing
 *
 * For sub-packages only:
 * - Disable issues
 * - Disable discussions
 * - Disable pull requests
 *
 * @param non-empty-string $token GitHub API token.
 *
 * @return bool True if all fixes succeeded.
 */
function fix(MonolithicRepository $monorepo, #[SensitiveParameter] string $token): bool
{
    $org = 'php-standard-library';
    $mainRepo = 'php-standard-library';
    $homepage = 'https://php-standard-library.dev';

    $httpClient = new Client\Client();

    $readHeaders = Message\FieldMap::from([
        ['authorization', 'Bearer ' . $token],
        ['accept', 'application/vnd.github+json'],
        ['x-github-api-version', '2022-11-28'],
        ['User-Agent', $org],
    ]);

    $writeHeaders = Message\FieldMap::from([
        ['authorization', 'Bearer ' . $token],
        ['accept', 'application/vnd.github+json'],
        ['x-github-api-version', '2022-11-28'],
        ['User-Agent', $org],
        ['content-type', 'application/json'],
    ]);

    $repos = Vec\map::<int, Package, string>($monorepo->packages, static fn(Package $p): string => Str\after($p->name, $org . '/') ?? $p->name);
    $repos[] = $mainRepo;

    $packagesBySlug = [];
    foreach ($monorepo->packages as $package) {
        $slug = Str\after($package->name, $org . '/') ?? $package->name;
        $packagesBySlug[$slug] = $package;
    }

    Log\info('Fixing %d repositories...', Iter\count::<string>($repos));

    $orgRulesetsTx = $httpClient->send(new Message\Request(
        method: Message\METHOD_GET,
        url: URL\parse(Str\format('https://api.github.com/orgs/%s/rulesets', $org)),
        headers: $readHeaders,
    ));

    $fetchAwaitables = Dict\from_keys::<string, Async\Awaitable<array>>($repos, static fn(string $repo): Async\Awaitable<array> => Async\run::<array>(static fn(): array => Async\concurrently::<string, Message\Transaction>([
        'settings' => static fn() => $httpClient->send(new Message\Request(
            method: Message\METHOD_GET,
            url: URL\parse(Str\format('https://api.github.com/repos/%s/%s', $org, $repo)),
            headers: $readHeaders,
        )),
        'repoRulesets' => static fn() => $httpClient->send(new Message\Request(
            method: Message\METHOD_GET,
            url: URL\parse(Str\format('https://api.github.com/repos/%s/%s/rulesets', $org, $repo)),
            headers: $readHeaders,
        )),
    ])));

    $settingsType = Type\shape::<string, string|null|array|bool>([
        'description' => Type\nullable::<string>(Type\string()),
        'homepage' => Type\nullable::<string>(Type\string()),
        'topics' => Type\vec::<string>(Type\non_empty_string()),
        'has_issues' => Type\bool(),
        'has_wiki' => Type\bool(),
        'has_discussions' => Type\bool(),
    ], allowUnknownFields: true);

    $rulesetType = Type\vec::<array>(Type\shape::<string, string>([
        'name' => Type\non_empty_string(),
    ], allowUnknownFields: true));

    $ok = true;
    $fixAwaitables = [];

    foreach ($fetchAwaitables as $repo => $awaitable) {
        $fetched = $awaitable->await();

        $isMain = $repo === $mainRepo;
        $full = $org . '/' . $repo;

        if ($fetched['settings']->response->status !== Message\STATUS_OK) {
            Log\error('%s returned %d, skipping', $full, $fetched['settings']->response->status);
            $ok = false;
            continue;
        }

        Log\step('inspecting', $full);

        $settings = Json\typed::<array>($fetched['settings']->response->body?->readAll() ?? '', $settingsType);

        $package = $packagesBySlug[$repo] ?? null;
        $expectedDescription = $isMain ? $monorepo->rootDescription : $package->description ?? '';
        $expectedKeywords = $isMain ? $monorepo->rootKeywords : $package->keywords ?? [];

        // Build settings PATCH payload
        $patch = [];

        if ($expectedDescription !== '' && ($settings['description'] ?? '') !== $expectedDescription) {
            $patch['description'] = $expectedDescription;
            Log\detail('  description: "%s" -> "%s"', $settings['description'] ?? '(none)', $expectedDescription);
        }

        if (($settings['homepage'] ?? '') !== $homepage) {
            $patch['homepage'] = $homepage;
            Log\detail('  homepage: "%s" -> "%s"', $settings['homepage'] ?? '(none)', $homepage);
        }

        if ($settings['has_wiki']) {
            $patch['has_wiki'] = false;
            Log\detail('  wiki: enabled -> disabled');
        }

        if (!$isMain) {
            if ($settings['has_issues']) {
                $patch['has_issues'] = false;
                Log\detail('  issues: enabled -> disabled');
            }

            if ($settings['has_discussions']) {
                $patch['has_discussions'] = false;
                Log\detail('  discussions: enabled -> disabled');
            }

            $patch['has_pull_requests'] = false;
        }

        $currentTopics = Vec\sort::<string>($settings['topics']);
        $expectedTopicsSorted = Vec\sort::<string>(Vec\map::<int, string, string>($expectedKeywords, static fn(string $k): string => Str\Byte\lowercase(
            $k,
        )));
        // @mago-expect analysis:impossible-type-comparison,impossible-type-comparison - FP!
        $needsTopics = $expectedTopicsSorted !== [] && $currentTopics !== $expectedTopicsSorted;

        $hasTagProtection = false;
        foreach ([$fetched['repoRulesets'], $orgRulesetsTx] as $rulesetsTx) {
            if ($rulesetsTx->response->status !== Message\STATUS_OK) {
                continue;
            }

            $rulesets = Json\typed::<array>($rulesetsTx->response->body?->readAll() ?? '', $rulesetType);
            foreach ($rulesets as $ruleset) {
                $name = Str\lowercase($ruleset['name']);
                if (Str\contains($name, 'tag') || Str\contains($name, 'immutable')) {
                    $hasTagProtection = true;
                    break 2;
                }
            }
        }

        $ops = [];
        if ($patch !== []) {
            $ops['settings'] = static fn() => $httpClient->send(new Message\Request(
                method: Message\METHOD_PATCH,
                url: URL\parse(Str\format('https://api.github.com/repos/%s/%s', $org, $repo)),
                headers: $writeHeaders,
                body: new IO\MemoryHandle(Json\encode($patch)),
            ));
        }

        if ($needsTopics) {
            Log\detail('  topics: [%s] -> [%s]', Str\join($currentTopics, ', '), Str\join($expectedTopicsSorted, ', '));
            $ops['topics'] = static fn() => $httpClient->send(new Message\Request(
                method: Message\METHOD_PUT,
                url: URL\parse(Str\format('https://api.github.com/repos/%s/%s/topics', $org, $repo)),
                headers: $writeHeaders,
                body: new IO\MemoryHandle(Json\encode(['names' => $expectedTopicsSorted])),
            ));
        }

        if (!$hasTagProtection) {
            Log\detail('  tag immutability: creating ruleset');
            $ops['ruleset'] = static fn() => $httpClient->send(new Message\Request(
                method: Message\METHOD_POST,
                url: URL\parse(Str\format('https://api.github.com/repos/%s/%s/rulesets', $org, $repo)),
                headers: $writeHeaders,
                body: new IO\MemoryHandle(Json\encode([
                    'name' => 'Tag Immutability',
                    'target' => 'tag',
                    'enforcement' => 'active',
                    'conditions' => ['ref_name' => ['include' => ['~ALL'], 'exclude' => []]],
                    'rules' => [['type' => 'deletion'], ['type' => 'update']],
                ])),
            ));
        }

        if ($ops !== []) {
            $fixAwaitables[$repo] = Async\run::<array>(static fn(): array => Async\concurrently::<string, Message\Transaction>($ops));
        } else {
            Log\success('%s already correct', $full);
        }
    }

    foreach ($fixAwaitables as $repo => $awaitable) {
        $results = $awaitable->await();
        $full = $org . '/' . $repo;

        $allGood = true;
        foreach ($results as $op => $tx) {
            if ($tx->response->status >= 200 && $tx->response->status < 300) {
                Log\info('%s %s fixed', $full, $op);
            } else {
                $body = $tx->response->body?->readAll() ?? '';
                Log\error('%s %s failed (HTTP %d): %s', $full, $op, $tx->response->status, $body);
                $allGood = false;
                $ok = false;
            }
        }

        if ($allGood) {
            Log\success('%s fixed', $full);
        }
    }

    return $ok;
}
