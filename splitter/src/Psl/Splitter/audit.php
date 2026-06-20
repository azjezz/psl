<?php

declare(strict_types=1);

namespace Psl\Splitter;

use Psl\Async;
use Psl\Dict;
use Psl\File;
use Psl\Filesystem;
use Psl\HTTP\Client;
use Psl\HTTP\Message;
use Psl\Iter;
use Psl\Json;
use Psl\Str;
use Psl\Type;
use Psl\URL;
use Psl\Vec;
use SensitiveParameter;

/**
 * Audit all package repositories in the monorepo.
 *
 * Derives the repo list from the monorepo's packages (not from the GitHub org).
 *
 * Checks:
 * - Description matches composer.json
 * - Homepage is set to the project website
 * - Topics (keywords) are present
 * - Wiki is disabled everywhere
 * - Issues are disabled on sub-packages
 * - Discussions are disabled on sub-packages
 * - Pull requests are disabled on sub-packages
 * - No open PRs on sub-packages
 * - Tag immutability rulesets exist
 *
 * @param non-empty-string $token GitHub API token.
 *
 * @return bool True if all checks pass.
 */
function audit(MonolithicRepository $monorepo, #[SensitiveParameter] string $token): bool
{
    $org = 'php-standard-library';
    $mainRepo = 'php-standard-library';
    $expectedHomepage = 'https://php-standard-library.dev';

    $httpClient = new Client\Client();

    $headers = Message\FieldMap::from([
        ['authorization', 'Bearer ' . $token],
        ['accept', 'application/vnd.github+json'],
        ['x-github-api-version', '2022-11-28'],
        ['User-Agent', $org],
    ]);

    $repos = Vec\map::<int, Package, string>($monorepo->packages, static fn(Package $p): string => Str\after($p->name, $org . '/') ?? $p->name);
    $repos[] = $mainRepo;

    $packagesBySlug = [];
    foreach ($monorepo->packages as $package) {
        $slug = Str\after($package->name, $org . '/') ?? $package->name;
        $packagesBySlug[$slug] = $package;
    }

    Log\info('Auditing %d repositories...', Iter\count::<string>($repos));

    $orgRulesetsTx = $httpClient->send(new Message\Request(
        method: Message\METHOD_GET,
        url: URL\parse(Str\format('https://api.github.com/orgs/%s/rulesets', $org)),
        headers: $headers,
    ));

    $awaitables = Dict\from_keys::<string, Async\Awaitable<array>>($repos, static fn(string $repo): Async\Awaitable<array> => Async\run::<array>(static fn(): array => Async\concurrently::<int, Message\Transaction>([
        static fn() => $httpClient->send(new Message\Request(
            method: Message\METHOD_GET,
            url: URL\parse(Str\format('https://api.github.com/repos/%s/%s', $org, $repo)),
            headers: $headers,
        )),
        static fn() => $httpClient->send(new Message\Request(
            method: Message\METHOD_GET,
            url: URL\parse(Str\format('https://api.github.com/repos/%s/%s/rulesets', $org, $repo)),
            headers: $headers,
        )),
        static fn() => $httpClient->send(new Message\Request(
            method: Message\METHOD_GET,
            url: URL\parse(Str\format('https://api.github.com/repos/%s/%s/pulls?state=open&per_page=1', $org, $repo)),
            headers: $headers,
        )),
    ])));

    $ok = true;

    $settingsType = Type\shape::<string, string|null|array|bool>([
        'description' => Type\nullable::<string>(Type\string()),
        'homepage' => Type\nullable::<string>(Type\string()),
        'topics' => Type\vec::<string>(Type\string()),
        'has_issues' => Type\bool(),
        'has_wiki' => Type\bool(),
        'has_discussions' => Type\bool(),
    ], allowUnknownFields: true);

    $rulesetType = Type\vec::<array>(Type\shape::<string, string>([
        'name' => Type\non_empty_string(),
    ], allowUnknownFields: true));

    $prType = Type\vec::<array>(Type\shape::<string, int>([
        'number' => Type\int(),
    ], allowUnknownFields: true));

    foreach ($awaitables as $repo => $awaitable) {
        [$settingsTx, $repoRulesetsTx, $prTx] = $awaitable->await();

        $isMain = $repo === $mainRepo;
        $full = $org . '/' . $repo;

        if ($settingsTx->response->status !== Message\STATUS_OK) {
            Log\error('%s returned %d', $full, $settingsTx->response->status);
            $ok = false;
            continue;
        } else {
            Log\step('auditing', $full);
        }

        $settings = Json\typed::<array>($settingsTx->response->body?->readAll() ?? '', $settingsType);

        $expectedDescription = $isMain ? $monorepo->rootDescription : $packagesBySlug[$repo]->description;
        if ($expectedDescription !== '' && ($settings['description'] ?? '') !== $expectedDescription) {
            Log\error(
                '%s description mismatch: "%s" (expected "%s")',
                $full,
                $settings['description'] ?? '(none)',
                $expectedDescription,
            );
            $ok = false;
        } else {
            Log\info('%s description ok', $full);
        }

        if (!$isMain && $expectedDescription !== '') {
            $package = $packagesBySlug[$repo] ?? null;
            $readmePath = ($package->path ?? '') . '/README.md';
            if (Filesystem\is_file($readmePath)) {
                $readmeContent = File\read($readmePath);
                if (!Str\contains($readmeContent, $expectedDescription)) {
                    Log\error('%s README.md does not contain composer.json description', $full);
                    $ok = false;
                } else {
                    Log\info('%s README.md description ok', $full);
                }
            } else {
                Log\error('%s README.md not found', $full);
                $ok = false;
            }
        }

        if (($settings['homepage'] ?? '') !== $expectedHomepage) {
            Log\error(
                '%s homepage is "%s" (expected "%s")',
                $full,
                $settings['homepage'] ?? '(none)',
                $expectedHomepage,
            );
            $ok = false;
        } else {
            Log\info('%s homepage ok', $full);
        }

        if ($settings['topics'] === []) {
            Log\error('%s has no topics', $full);
            $ok = false;
        } else {
            Log\info('%s has %d topic(s)', $full, Iter\count::<string>($settings['topics']));
        }

        if ($settings['has_wiki']) {
            Log\error('%s has wiki enabled', $full);
            $ok = false;
        } else {
            Log\info('%s has wiki disabled', $full);
        }

        if (!$isMain) {
            if ($settings['has_issues']) {
                Log\error('%s has issues enabled (sub-package)', $full);
                $ok = false;
            } else {
                Log\info('%s has issues disabled', $full);
            }

            if ($settings['has_discussions']) {
                Log\error('%s has discussions enabled (sub-package)', $full);
                $ok = false;
            } else {
                Log\info('%s has discussions disabled', $full);
            }

            if ($prTx->response->status === Message\STATUS_OK) {
                $ok = false;
                $prs = Json\typed::<array>($prTx->response->body?->readAll() ?? '', $prType);
                if ($prs !== []) {
                    Log\error('%s has open pull request(s) (sub-package)', $full);
                } else {
                    Log\error('%s has pull request(s) enabled (sub-package)', $full);
                }
            } else {
                Log\info('%s has pull request(s) disabled', $full);
            }
        }

        $hasTagProtection = false;
        foreach ([$repoRulesetsTx, $orgRulesetsTx] as $rulesetsTx) {
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

        if (!$hasTagProtection) {
            Log\warn('%s has no tag immutability ruleset', $full);
            $ok = false;
        } else {
            Log\info('%s has tag protection enabled', $full);
        }

        Log\success('%s audited', $full);
    }

    return $ok;
}
