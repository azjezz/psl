<?php

declare(strict_types=1);

namespace Psl\Splitter;

use Psl\Async;
use Psl\Dict;
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
 * - Wiki is disabled everywhere
 * - Issues are disabled on sub-packages
 * - Discussions are disabled on sub-packages
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

    $httpClient = new Client\Client();

    $headers = Message\FieldMap::from([
        ['authorization', 'Bearer ' . $token],
        ['accept', 'application/vnd.github+json'],
        ['x-github-api-version', '2022-11-28'],
        ['User-Agent', $org],
    ]);

    // Build repo list from monorepo packages + the main repo
    $repos = Vec\map($monorepo->packages, static fn(Package $p): string => Str\after($p->name, $org . '/') ?? $p->name);
    $repos[] = $mainRepo;

    Log\info('Auditing %d repositories...', Iter\count($repos));

    $awaitables = Dict\from_keys($repos, static fn(string $repo): Async\Awaitable => Async\run(static fn(): array => Async\concurrently([
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
            url: URL\parse(Str\format('https://api.github.com/orgs/%s/rulesets', $org)),
            headers: $headers,
        )),
        static fn() => $httpClient->send(new Message\Request(
            method: Message\METHOD_GET,
            url: URL\parse(Str\format('https://api.github.com/repos/%s/%s/pulls?state=open&per_page=1', $org, $repo)),
            headers: $headers,
        )),
    ])));

    $ok = true;

    $settingsType = Type\shape([
        'has_issues' => Type\bool(),
        'has_wiki' => Type\bool(),
        'has_discussions' => Type\bool(),
    ], allowUnknownFields: true);

    $rulesetType = Type\vec(Type\shape([
        'name' => Type\non_empty_string(),
    ], allowUnknownFields: true));

    $prType = Type\vec(Type\shape([
        'number' => Type\int(),
    ], allowUnknownFields: true));

    foreach ($awaitables as $repo => $awaitable) {
        [$settingsTx, $repoRulesetsTx, $orgRulesetsTx, $prTx] = $awaitable->await();

        $isMain = $repo === $mainRepo;
        $full = $org . '/' . $repo;

        if ($settingsTx->response->status !== Message\STATUS_OK) {
            Log\error('%s returned %d', $full, $settingsTx->response->status);
            $ok = false;
            continue;
        } else {
            Log\step('auditing', $full);
        }

        $settings = Json\typed($settingsTx->response->body?->readAll() ?? '', $settingsType);

        // Wiki: disabled everywhere
        if ($settings['has_wiki']) {
            Log\error('%s has wiki enabled', $full);
            $ok = false;
        } else {
            Log\info('%s has wiki disabled', $full);
        }

        // Sub-packages only
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
                $prs = Json\typed($prTx->response->body?->readAll() ?? '', $prType);
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

            $rulesets = Json\typed($rulesetsTx->response->body?->readAll() ?? '', $rulesetType);
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
