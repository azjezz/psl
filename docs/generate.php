<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;
use Psl\Async;
use Psl\Env;
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

const SITE_URL = 'https://php-standard-library.dev';

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
    'either-or-both' => 'php-standard-library/either-or-both',
    'encoding' => 'php-standard-library/encoding',
    'env' => 'php-standard-library/env',
    'file' => 'php-standard-library/file',
    'filesystem' => 'php-standard-library/filesystem',
    'fun' => 'php-standard-library/fun',
    'graph' => 'php-standard-library/graph',
    'h2' => 'php-standard-library/h2',
    'hash' => 'php-standard-library/hash',
    'hpack' => 'php-standard-library/hpack',
    'http-client' => 'php-standard-library/http-client',
    'http-message' => 'php-standard-library/http-message',
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
    'tools' => 'Tools',
];

const CATEGORY_COLORS = [
    'Basics' => '#e63946',
    'Types & Error Handling' => '#457b9d',
    'Async' => '#2a9d8f',
    'Collections' => '#e9c46a',
    'Text & Encoding' => '#f4a261',
    'I/O' => '#264653',
    'Identifiers' => '#3d5a80',
    'Networking' => '#6a4c93',
    'Protocols' => '#0891b2',
    'Terminal' => '#1d3557',
    'Security' => '#d62828',
    'System' => '#606c38',
    'Other' => '#666',
];

/**
 * Homepage feature highlights (ported verbatim from the previous client-side app.js).
 *
 * @var list<array{title: string, description: string, code: string, links: list<array{0: string, 1: string}>}>
 */
const FEATURES = [
    [
        'title' => 'Type-Safe from Input to Output',
        'description' => 'Validate and coerce untrusted data with composable type combinators. Shapes, unions, optionals: all with zero reflection overhead.',
        'code' => <<<'PHP'
        use Psl\Type;

        $userType = Type\shape([
            'name' => Type\non_empty_string(),
            'age'  => Type\positive_int(),
            'tags' => Type\vec(Type\string()),
        ]);

        $user = $userType->coerce($untrustedInput);
        // array{name: non-empty-string,
        //   age: positive-int, tags: list<string>}
        PHP,
        'links' => [['Type', 'type'], ['Result', 'result'], ['Option', 'option']],
    ],
    [
        'title' => 'Async Without the Ceremony',
        'description' => 'Run concurrent operations with a single function call. Structured concurrency built on fibers. No promises, no callbacks.',
        'code' => <<<'PHP'
        use Psl\Async;
        use Psl\IO;

        Async\main(static function(): int {
            [$a, $b, $c] = Async\concurrently([
                static fn() => Async\sleep(0.1),
                static fn() => Async\sleep(0.2),
                static fn() => Async\sleep(0.1),
            ]);

            IO\write_error_line('Done in ~0.2s, not 0.4s');

            return 0;
        });
        PHP,
        'links' => [
            ['Async',   'async'],
            ['Channel', 'channel'],
            ['IO',      'io'],
        ],
    ],
    [
        'title' => 'Collections That Make Sense',
        'description' => 'Map, filter, sort, and reshape arrays with pure functions. Separate return types for lists and dicts. No more array key confusion.',
        'code' => <<<'PHP'
        use Psl\Vec;
        use Psl\Dict;
        use Psl\Str;

        $names = ['alice', 'bob', 'charlie', 'dave'];

        Vec\map($names, Str\uppercase(...));
        // ['ALICE', 'BOB', 'CHARLIE', 'DAVE']

        Vec\filter($names, fn($n) => Str\length($n) > 3);
        // ['alice', 'charlie', 'dave']

        Dict\pull($names, Str\uppercase(...), fn($n) => $n);
        // {alice: 'ALICE', bob: 'BOB', ...}
        PHP,
        'links' => [['Vec', 'vec'], ['Dict', 'dict'], ['Iter', 'iter']],
    ],
    [
        'title' => 'TCP Server in 10 Lines',
        'description' => 'Production-ready networking primitives. TCP, TLS, UDP, Unix sockets: all async, all composable.',
        'code' => <<<'PHP'
        use Psl\Async;
        use Psl\TCP;
        use Psl\IO;

        Async\main(static function(): int {
            $server = TCP\listen('127.0.0.1', 8080);
            IO\write_error_line('Listening on :8080');

            while (true) {
                $conn = $server->accept();
                Async\run(static function() use ($conn) {
                    $conn->writeAll("Hello!\n");
                    $conn->close();
                })->ignore();
            }
        });
        PHP,
        'links' => [
            ['TCP',     'tcp'],
            ['Network', 'network'],
            ['Async',   'async'],
        ],
    ],
];

/**
 * Escape text destined for an HTML text node (matches the previous JS escape_html).
 */
function escape_html(string $value): string
{
    return Str\replace(Str\replace(Str\replace($value, '&', '&amp;'), '<', '&lt;'), '>', '&gt;');
}

/**
 * Escape a value destined for a double-quoted HTML attribute.
 */
function escape_attr(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

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

/**
 * Derive a short card description from a doc's markdown body
 * (ported verbatim from the previous client-side extract_descriptions).
 */
function extract_description(string $markdown): string
{
    $lines = Str\split($markdown, "\n");
    foreach (Vec\slice($lines, 1) as $line) {
        $trimmed = Str\trim($line);
        if (Str\length($trimmed) <= 10 || Regex\matches($trimmed, '/^[#`|*-]/')) {
            continue;
        }

        $clean = Regex\replace($trimmed, '/\[([^\]]+)\]\([^)]+\)/', '$1');
        $clean = Regex\replace($clean, '/`([^`]+)`/', '$1');

        return Str\length($clean) > 100 ? Str\slice($clean, 0, 97) . '...' : $clean;
    }

    return '';
}

/**
 * Build the markdown converter (parity-verified against the previous marked.js setup:
 * gfm tables, autolinks, and soft line breaks rendered as newlines rather than <br>).
 */
function make_converter(): MarkdownConverter
{
    $environment = new Environment(['renderer' => ['soft_break' => "\n"]]);
    $environment->addExtension(new CommonMarkCoreExtension());
    $environment->addExtension(new TableExtension());
    $environment->addExtension(new AutolinkExtension());

    return new MarkdownConverter($environment);
}

/**
 * Render the sidebar navigation as static links.
 *
 * @param array<string, list<string>> $categories
 * @param array<string, string> $titles
 */
function render_nav(array $categories, array $titles, string $rel, string $home, string $activeSlug): string
{
    $homeActive = $activeSlug === '' ? ' active' : '';
    $html = '<a class="nav-link' . $homeActive . '" href="' . escape_attr($home) . '" data-slug="">Home</a>';

    foreach ($categories as $category => $slugs) {
        $html .= '<div class="nav-category">' . escape_html($category) . '</div>';
        foreach ($slugs as $slug) {
            $active = $slug === $activeSlug ? ' active' : '';
            $href = escape_attr($rel . $slug . '/');
            $title = escape_html($titles[$slug] ?? $slug);
            $html .=
                '<a class="nav-link'
                . $active
                . '" href="'
                . $href
                . '" data-slug="'
                . escape_attr($slug)
                . '">'
                . $title
                . '</a>';
        }
    }

    return $html;
}

/**
 * Render the homepage feature blocks (ported from the previous client-side build_features).
 */
function build_features(string $rel): string
{
    $html = '<div id="features">';
    foreach (FEATURES as $feature) {
        $links = '';
        foreach ($feature['links'] as [$label, $slug]) {
            $links .= '<a href="' . escape_attr($rel . $slug . '/') . '">' . escape_html($label) . '</a>';
        }

        $html .=
            '<div class="feature fade-in">'
            . '<div class="feature-code"><pre><code class="language-php">'
            . escape_html($feature['code'])
            . '</code></pre></div>'
            . '<div class="feature-text"><h3>'
            . escape_html($feature['title'])
            . '</h3>'
            . '<p>'
            . escape_html($feature['description'])
            . '</p>'
            . '<div class="feature-links">'
            . $links
            . '</div></div>'
            . '</div>';
    }

    return $html . '</div>';
}

function build_footer(): string
{
    return (
        '<div class="page-footer fade-in">'
        . '<p class="footer-license">MIT License &middot; Made by <a href="https://github.com/azjezz">azjezz</a> '
        . 'and <a href="https://github.com/php-standard-library/php-standard-library/graphs/contributors">contributors</a> '
        . '&middot; Sponsored by <a href="https://carthage.software">Carthage.Software</a></p>'
        . '</div>'
    );
}

/**
 * Render the homepage body (hero + features + component grid + footer).
 *
 * @param array<string, list<string>> $categories
 * @param array<string, string> $titles
 * @param array<string, string> $descriptions
 */
function build_front_page(array $categories, array $titles, array $descriptions, string $rel): string
{
    $firstSlug = Iter\first(Vec\keys(SLUG_TO_PACKAGE)) ?? 'type';
    $firstPackage = SLUG_TO_PACKAGE[$firstSlug];

    $html =
        '<div class="hero fade-in">'
        . '<h1>PSL</h1>'
        . '<p class="tagline">PHP Standard Library</p>'
        . '<p class="hero-description">A standard library for PHP, inspired by <a href="https://github.com/hhvm/hsl">hhvm/hsl</a>. '
        . 'Provides a consistent, centralized, well-typed set of APIs covering async, collections, networking, I/O, cryptography, '
        . 'terminal UI, and more - replacing PHP functions and primitives with safer, async-ready alternatives that error predictably.</p>'
        . '<div class="install-box-wrapper"><a id="rotating-install" class="install-box install-box-typing" href="'
        . escape_attr($rel . $firstSlug . '/')
        . '">'
        . 'composer require '
        . escape_html($firstPackage)
        . '<span class="typing-cursor"></span></a></div>'
        . '<p class="hero-separator">or get everything at once</p>'
        . '<div class="install-box-wrapper"><div class="install-box">composer require php-standard-library/php-standard-library</div></div>'
        . '<div class="hero-links">'
        . '<a href="https://github.com/php-standard-library/php-standard-library" class="hero-btn">GitHub</a>'
        . '<a href="https://github.com/sponsors/azjezz" class="hero-btn hero-btn-sponsor">Sponsor</a>'
        . '</div></div>';

    $html .= build_features($rel);

    foreach ($categories as $category => $slugs) {
        $color = CATEGORY_COLORS[$category] ?? '#000';
        $html .= '<div class="category-section fade-in">';
        $html .= '<h2 style="border-color:' . escape_attr($color) . '">' . escape_html($category) . '</h2>';
        $html .= '<div class="component-grid">';
        foreach ($slugs as $slug) {
            $title = escape_html($titles[$slug] ?? $slug);
            $desc = escape_html($descriptions[$slug] ?? '');
            $href = escape_attr($rel . $slug . '/');
            $html .=
                '<div class="component-card" style="--accent:'
                . escape_attr($color)
                . '">'
                . '<a href="'
                . $href
                . '"><h3>'
                . $title
                . '</h3><p>'
                . $desc
                . '</p></a></div>';
        }

        $html .= '</div></div>';
    }

    return $html . build_footer();
}

/**
 * Render the homepage as a self-contained markdown overview: description, install
 * command, and a categorized index of every component (mirrors the HTML front page),
 * served as index.md so an LLM can fetch the whole catalogue in one file.
 *
 * @param array<string, list<string>> $categories
 * @param array<string, string> $titles
 * @param array<string, string> $descriptions
 */
function build_front_page_markdown(array $categories, array $titles, array $descriptions): string
{
    $md =
        "# PSL - PHP Standard Library\n\n"
        . 'A standard library for PHP, inspired by [hhvm/hsl](https://github.com/hhvm/hsl). '
        . 'Provides a consistent, centralized, well-typed set of APIs covering async, collections, '
        . 'networking, I/O, cryptography, terminal UI, and more - replacing PHP functions and '
        . "primitives with safer, async-ready alternatives that error predictably.\n\n"
        . "## Installation\n\n"
        . "```\ncomposer require php-standard-library/php-standard-library\n```\n\n"
        . 'Each component is also available as a standalone package '
        . "(e.g. `composer require php-standard-library/type`).\n\n"
        . "## Components\n\n";

    foreach ($categories as $category => $slugs) {
        $md .= '### ' . $category . "\n\n";
        foreach ($slugs as $slug) {
            $title = $titles[$slug] ?? $slug;
            $desc = $descriptions[$slug] ?? '';
            $md .= '- [' . $title . '](' . $slug . '/index.md)';
            if ($desc !== '') {
                $md .= ' — ' . $desc;
            }

            $md .= "\n";
        }

        $md .= "\n";
    }

    return $md;
}

/**
 * Render the llms.txt curated index (https://llmstxt.org/): an H1, a blockquote
 * summary, then one section per category linking to each component's markdown using
 * absolute URLs so the file is portable when copied to the site root.
 *
 * @param array<string, list<string>> $categories
 * @param array<string, string> $titles
 * @param array<string, string> $descriptions
 */
function build_llms_index(
    array $categories,
    array $titles,
    array $descriptions,
    string $base,
    bool $withFullLink = true,
): string {
    $md =
        "# PSL - PHP Standard Library\n\n"
        . '> A standard library for PHP, inspired by hhvm/hsl: a consistent, centralized, '
        . 'well-typed set of APIs covering async, collections, networking, I/O, cryptography, '
        . 'terminal UI, and more, replacing PHP functions and primitives with safer, '
        . "async-ready alternatives that error predictably.\n\n"
        . 'Install everything with `composer require php-standard-library/php-standard-library`, '
        . 'or install any component as a standalone package. Each link below is the full '
        . "markdown documentation for that component.\n\n";

    foreach ($categories as $category => $slugs) {
        $md .= '## ' . $category . "\n\n";
        foreach ($slugs as $slug) {
            $title = $titles[$slug] ?? $slug;
            $desc = $descriptions[$slug] ?? '';
            $md .= '- [' . $title . '](' . $base . $slug . '/index.md)';
            if ($desc !== '') {
                $md .= ': ' . $desc;
            }

            $md .= "\n";
        }

        $md .= "\n";
    }

    if ($withFullLink) {
        $md .=
            "## Full documentation\n\n"
            . '- [Complete library documentation, single file]('
            . $base
            . 'llms-full.txt): '
            . "every component's markdown concatenated into one file.\n";
    }

    return $md;
}

/**
 * Render llms-full.txt: the llms.txt index followed by every component's fully-processed
 * markdown inlined, so an agent can ingest the whole library (overview + all docs) in one
 * fetch.
 *
 * @param array<string, list<string>> $categories
 * @param array<string, string> $docs
 */
function build_llms_full(string $index, array $categories, array $docs): string
{
    $parts = [Str\trim($index)];
    foreach ($categories as $slugs) {
        foreach ($slugs as $slug) {
            if (!isset($docs[$slug])) {
                continue;
            }

            $parts[] = Str\trim($docs[$slug]);
        }
    }

    return Str\join($parts, "\n\n---\n\n") . "\n";
}

/**
 * Assemble a full HTML page from the shared template.
 *
 * @param array<string, string> $replacements
 */
function render_page(string $template, array $replacements): string
{
    $html = $template;
    foreach ($replacements as $needle => $value) {
        $html = Str\replace($html, '{{' . $needle . '}}', $value);
    }

    return $html;
}

Async\main(static function (): int {
    $gitRef = Env\get_var('GITHUB_REF_NAME') ?? 'next';

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
    $descriptions = [];
    foreach ($docs as $slug => $content) {
        $match = Regex\first_match($content, '/^#\s+(.+)/m');
        $titles[$slug] = $match !== null ? $match[1] : $slug;
        $descriptions[$slug] = namespace\extract_description($content);
    }

    $converter = namespace\make_converter();
    $template = File\read(RESOURCES_DIR . '/page.html');
    // Encoded for embedding inside a <script> block: hex-escape <, >, ', " so the data
    // can never break out of the script context (e.g. a literal </script>).
    $scriptJsonFlags = \JSON_HEX_TAG | \JSON_HEX_APOS | \JSON_HEX_QUOT | \JSON_HEX_AMP;
    $packagesJson = Json\encode(SLUG_TO_PACKAGE, false, $scriptJsonFlags);
    $versionJson = Json\encode($gitRef, false, $scriptJsonFlags);

    if (!Filesystem\is_directory(OUTPUT_DIR)) {
        Filesystem\create_directory(OUTPUT_DIR);
    }

    $sitemapUrls = [SITE_URL . '/' . $gitRef . '/'];

    // Component pages: dist/{slug}/index.html
    foreach ($docs as $slug => $content) {
        $rel = '../';
        $article = (string) $converter->convert($content)->getContent();

        $package = SLUG_TO_PACKAGE[$slug] ?? null;
        if ($package !== null) {
            $installBox =
                '<div class="install-box-wrapper"><div class="install-box">composer require '
                . namespace\escape_html($package)
                . '</div></div>';
            $headingEnd = Str\search($article, '</h1>');
            if ($headingEnd !== null) {
                $insertAt = $headingEnd + Str\length('</h1>');
                $article = Str\slice($article, 0, $insertAt) . $installBox . Str\slice($article, $insertAt);
            }
        }

        $title = $titles[$slug] ?? $slug;
        $canonical = SITE_URL . '/' . $gitRef . '/' . $slug . '/';
        // Point at the fully-processed markdown served next to this page (index.md),
        // not the GitHub source: the local copy has @example() directives expanded and
        // source links rewritten, giving an LLM one complete, self-contained file.
        $headExtra = '        <link rel="alternate" type="text/markdown" href="index.md" title="Markdown source">';

        $page = namespace\render_page($template, [
            'TITLE' => namespace\escape_attr($title . ' - PSL'),
            'DESCRIPTION' => namespace\escape_attr($descriptions[$slug] ?? ''),
            'CANONICAL' => namespace\escape_attr($canonical),
            'HEAD_EXTRA' => $headExtra,
            'REL' => $rel,
            'HOME' => $rel,
            'NAV' => namespace\render_nav($categories, $titles, $rel, $rel, $slug),
            'BODY' => $article,
            'VERSION' => $versionJson,
            'INLINE_DATA' => '',
        ]);

        $slugDir = OUTPUT_DIR . '/' . $slug;
        if (!Filesystem\is_directory($slugDir)) {
            Filesystem\create_directory($slugDir);
        }

        File\write($slugDir . '/index.html', $page, File\WriteMode::Truncate);
        File\write($slugDir . '/index.md', $content, File\WriteMode::Truncate);
        $sitemapUrls[] = $canonical;
    }

    // Front page: dist/index.html
    $rel = '';
    $home = './';
    $frontBody = namespace\build_front_page($categories, $titles, $descriptions, $rel);
    $frontPage = namespace\render_page($template, [
        'TITLE' => 'PSL - PHP Standard Library',
        'DESCRIPTION' => 'PSL is a batteries-included standard library for PHP covering async, collections, networking, I/O, cryptography, terminal UI, and more.',
        'CANONICAL' => namespace\escape_attr(SITE_URL . '/' . $gitRef . '/'),
        'HEAD_EXTRA' => '        <link rel="alternate" type="text/markdown" href="index.md" title="Markdown overview">',
        'REL' => $rel,
        'HOME' => $home,
        'NAV' => namespace\render_nav($categories, $titles, $rel, $home, ''),
        'BODY' => $frontBody,
        'VERSION' => $versionJson,
        'INLINE_DATA' => '            const PACKAGES=' . $packagesJson . ';',
    ]);

    File\write(OUTPUT_DIR . '/index.html', $frontPage, File\WriteMode::Truncate);
    File\write(
        OUTPUT_DIR . '/index.md',
        namespace\build_front_page_markdown($categories, $titles, $descriptions),
        File\WriteMode::Truncate,
    );

    // llms.txt (curated index) + llms-full.txt (whole library in one file) for coding
    // agents. Use absolute URLs so these stay valid when copied to the gh-pages root.
    $absoluteBase = SITE_URL . '/' . $gitRef . '/';
    $llmsIndex = namespace\build_llms_index($categories, $titles, $descriptions, $absoluteBase);
    File\write(OUTPUT_DIR . '/llms.txt', $llmsIndex, File\WriteMode::Truncate);
    File\write(
        OUTPUT_DIR . '/llms-full.txt',
        namespace\build_llms_full(
            namespace\build_llms_index($categories, $titles, $descriptions, $absoluteBase, false),
            $categories,
            $docs,
        ),
        File\WriteMode::Truncate,
    );

    // Per-version sitemap (referenced from the gh-pages root sitemap index).
    // A version is published as one immutable snapshot, so every page shares a single
    // lastmod: the build date, not a per-file change date.
    $lastmod = gmdate('Y-m-d');
    $sitemap =
        '<?xml version="1.0" encoding="UTF-8"?>'
        . "\n"
        . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
        . "\n";
    foreach ($sitemapUrls as $url) {
        $sitemap .=
            '  <url><loc>' . namespace\escape_html($url) . '</loc><lastmod>' . $lastmod . '</lastmod></url>' . "\n";
    }

    $sitemap .= '</urlset>' . "\n";
    File\write(OUTPUT_DIR . '/sitemap.xml', $sitemap, File\WriteMode::Truncate);

    // Assets
    $assetsDir = OUTPUT_DIR . '/assets';
    if (!Filesystem\is_directory($assetsDir)) {
        Filesystem\create_directory($assetsDir);
    }

    File\write($assetsDir . '/style.css', File\read(RESOURCES_DIR . '/style.css'), File\WriteMode::Truncate);
    File\write($assetsDir . '/app.js', File\read(RESOURCES_DIR . '/app.js'), File\WriteMode::Truncate);
    File\write($assetsDir . '/banner.png', File\read(RESOURCES_DIR . '/banner.png'), File\WriteMode::Truncate);

    IO\write_line('Generated %s (%d component pages + front page)', OUTPUT_DIR, Iter\count($docs));

    return 0;
});
