<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Psl\Async;
use Psl\File;
use Psl\Filesystem;
use Psl\IO;
use Psl\Iter;
use Psl\Json;
use Psl\Regex;
use Psl\Str;
use Psl\Vec;

const DOCUMENTATION_DIR = __DIR__;

const CONTENT_DIR = DOCUMENTATION_DIR . '/content';

const EXAMPLES_DIR = DOCUMENTATION_DIR . '/examples';

const RESOURCES_DIR = DOCUMENTATION_DIR . '/resources';

/**
 * @var non-empty-string
 */
const OUTPUT_DIR = DOCUMENTATION_DIR . '/dist';

/**
 * @var non-empty-string
 */
const OUTPUT_FILE = OUTPUT_DIR . '/index.html';

/**
 * Map doc slugs to their Composer package names.
 * Slugs not in this map don't get an install command.
 *
 * @mago-expect lint:no-literal-password
 */
const SLUG_TO_PACKAGE = [
    'foundation' => 'php-standard-library/foundation',
    'ansi' => 'php-standard-library/ansi',
    'async' => 'php-standard-library/async',
    'binary' => 'php-standard-library/binary',
    'cache' => 'php-standard-library/cache',
    'channel' => 'php-standard-library/channel',
    'cidr' => 'php-standard-library/cidr',
    'class' => 'php-standard-library/class',
    'collection' => 'php-standard-library/collection',
    'comparison' => 'php-standard-library/comparison',
    'compression' => 'php-standard-library/compression',
    'crypto' => 'php-standard-library/crypto',
    'data-structure' => 'php-standard-library/data-structure',
    'date-time' => 'php-standard-library/date-time',
    'default' => 'php-standard-library/default',
    'dns' => 'php-standard-library/dns',
    'dnssec' => 'php-standard-library/dnssec',
    'dict' => 'php-standard-library/dict',
    'either' => 'php-standard-library/either',
    'encoding' => 'php-standard-library/encoding',
    'env' => 'php-standard-library/env',
    'file' => 'php-standard-library/file',
    'filesystem' => 'php-standard-library/filesystem',
    'fun' => 'php-standard-library/fun',
    'graph' => 'php-standard-library/graph',
    'h2' => 'php-standard-library/h2',
    'hash' => 'php-standard-library/hash',
    'hpack' => 'php-standard-library/hpack',
    'html' => 'php-standard-library/html',
    'interface' => 'php-standard-library/interface',
    'interoperability' => 'php-standard-library/interoperability',
    'io' => 'php-standard-library/io',
    'ip' => 'php-standard-library/ip',
    'iter' => 'php-standard-library/iter',
    'json' => 'php-standard-library/json',
    'locale' => 'php-standard-library/locale',
    'math' => 'php-standard-library/math',
    'mime' => 'php-standard-library/mime',
    'message' => 'php-standard-library/message',
    'network' => 'php-standard-library/network',
    'observer' => 'php-standard-library/observer',
    'option' => 'php-standard-library/option',
    'os' => 'php-standard-library/os',
    'password' => 'php-standard-library/password',
    'process' => 'php-standard-library/process',
    'promise' => 'php-standard-library/promise',
    'pseudo-random' => 'php-standard-library/pseudo-random',
    'punycode' => 'php-standard-library/punycode',
    'random-sequence' => 'php-standard-library/random-sequence',
    'range' => 'php-standard-library/range',
    'regex' => 'php-standard-library/regex',
    'result' => 'php-standard-library/result',
    'runtime' => 'php-standard-library/runtime',
    'secure-random' => 'php-standard-library/secure-random',
    'smtp' => 'php-standard-library/smtp',
    'shell' => 'php-standard-library/shell',
    'socks' => 'php-standard-library/socks',
    'str' => 'php-standard-library/str',
    'tcp' => 'php-standard-library/tcp',
    'terminal' => 'php-standard-library/terminal',
    'tls' => 'php-standard-library/tls',
    'trait' => 'php-standard-library/trait',
    'tree' => 'php-standard-library/tree',
    'type' => 'php-standard-library/type',
    'udp' => 'php-standard-library/udp',
    'unix' => 'php-standard-library/unix',
    'uri' => 'php-standard-library/uri',
    'url' => 'php-standard-library/url',
    'iri' => 'php-standard-library/iri',
    'vec' => 'php-standard-library/vec',
];

const CATEGORY_DISPLAY_NAMES = [
    'basics' => 'Basics',
    'types' => 'Types & Error Handling',
    'async' => 'Async',
    'collections' => 'Collections',
    'text' => 'Text & Encoding',
    'io' => 'I/O',
    'identifiers' => 'Identifiers',
    'networking' => 'Networking',
    'protocols' => 'Protocols',
    'terminal' => 'Terminal',
    'security' => 'Security',
    'system' => 'System',
    'other' => 'Other',
];

/**
 * Process @example() directives in markdown content.
 *
 * Replaces lines like `@example('basics/vec-mapping.php')` with the
 * contents of the corresponding example file (minus the 6-line preamble),
 * wrapped in php fences.
 */
function process_markdown(string $markdown): string
{
    return Regex\replace_with($markdown, "/^@example\('([^']+)'\)$/m", function (array $matches): string {
        $examplePath = EXAMPLES_DIR . '/' . $matches[1];
        if (!Filesystem\exists($examplePath)) {
            IO\write_error_line('WARNING: Example file not found: %s', $examplePath);
            return $matches[0];
        }

        $code = File\read($examplePath);
        $lines = Str\split($code, "\n");
        $code = Str\join(Vec\slice($lines, 6), "\n");
        $code = Str\trim_right($code);

        return "```php\n" . $code . "\n```";
    });
}

/**
 * Scan a directory for files with a given extension, returning sorted full paths.
 *
 * @param non-empty-string $directory
 * @param non-empty-string $extension
 *
 * @return list<non-empty-string>
 */
function scan_for_files(string $directory, string $extension): array
{
    if (!Filesystem\is_directory($directory)) {
        return [];
    }

    $entries = Filesystem\read_directory($directory);
    $filtered = Vec\filter($entries, fn(string $f): bool => Str\ends_with($f, $extension));

    return Vec\sort($filtered);
}

Async\main(static function (): int {
    $gitRef = Psl\Env\get_var('GITHUB_REF_NAME') ?? 'next';

    $repoBaseUrl = 'https://github.com/php-standard-library/php-standard-library/tree/' . $gitRef . '/';

    $nsToPackageDir = [];
    foreach (SLUG_TO_PACKAGE as $slug => $package) {
        $packageDir = Str\after($package, 'php-standard-library/') ?? $slug;
        $pslDir = DOCUMENTATION_DIR . '/../packages/' . $packageDir . '/src/Psl';
        if (Filesystem\is_directory($pslDir)) {
            foreach (Filesystem\read_directory($pslDir) as $entry) {
                if (!Filesystem\is_directory($entry)) {
                    continue;
                }

                $nsToPackageDir[Filesystem\get_basename($entry)] = $packageDir;
            }
        }
    }

    $nsToPackageDir[''] = 'foundation';

    $docs = [];
    $categories = [];
    foreach (CATEGORY_DISPLAY_NAMES as $dirName => $displayName) {
        $categorySlugs = [];
        foreach (scan_for_files(CONTENT_DIR . '/' . $dirName, '.md') as $file) {
            $slug = Filesystem\get_basename($file, '.md');
            $content = File\read($file);
            $content = namespace\process_markdown($content);
            $content = Regex\replace_with(
                $content,
                '/`(src\/Psl\/([^\/`]+)\/[^`]*)`/',
                static function (array $matches) use ($repoBaseUrl, $nsToPackageDir): string {
                    $fullPath = $matches[1];
                    $namespace = $matches[2];
                    $packageDir = $nsToPackageDir[$namespace] ?? null;

                    if ($packageDir !== null) {
                        $url = $repoBaseUrl . 'packages/' . $packageDir . '/' . $fullPath;
                    } else {
                        $url = $repoBaseUrl . $fullPath;
                    }

                    return '[' . $fullPath . '](' . $url . ')';
                },
            );
            $content = Regex\replace($content, '/`(src\/Psl\/)`/', '[$1](' . $repoBaseUrl . 'packages/foundation/$1)');
            $docs[$slug] = $content;
            $categorySlugs[] = $slug;
        }

        if ($categorySlugs !== []) {
            $categories[$displayName] = $categorySlugs;
        }
    }

    $titles = [];
    foreach ($docs as $slug => $content) {
        $match = Regex\first_match($content, '/^#\s+(.+)/m');
        $titles[$slug] = $match !== null ? $match[1] : $slug;
    }

    $docsJson = Str\replace(Json\encode($docs), '/', '\\/');
    $categoriesJson = Str\replace(Json\encode($categories), '/', '\\/');
    $titlesJson = Str\replace(Json\encode($titles), '/', '\\/');
    $packagesJson = Str\replace(Json\encode(SLUG_TO_PACKAGE), '/', '\\/');

    $template = File\read(RESOURCES_DIR . '/template.html');

    $html = Str\replace($template, '{{VERSION}}', $gitRef);
    $html = Str\replace($html, '{{DOCS}}', $docsJson);
    $html = Str\replace($html, '{{CATEGORIES}}', $categoriesJson);
    $html = Str\replace($html, '{{TITLES}}', $titlesJson);
    $html = Str\replace($html, '{{PACKAGES}}', $packagesJson);

    if (!Filesystem\is_directory(OUTPUT_DIR)) {
        Filesystem\create_directory(OUTPUT_DIR);
    }

    $assetsDir = OUTPUT_DIR . '/assets';
    if (!Filesystem\is_directory($assetsDir)) {
        Filesystem\create_directory($assetsDir);
    }

    File\write(OUTPUT_FILE, $html, File\WriteMode::Truncate);
    File\write($assetsDir . '/style.css', File\read(RESOURCES_DIR . '/style.css'), File\WriteMode::Truncate);
    File\write($assetsDir . '/app.js', File\read(RESOURCES_DIR . '/app.js'), File\WriteMode::Truncate);
    File\write($assetsDir . '/banner.png', File\read(RESOURCES_DIR . '/banner.png'), File\WriteMode::Truncate);

    IO\write_line('Generated %s (%d components)', OUTPUT_FILE, Iter\count($docs));

    return 0;
});
