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

const CATEGORY_DISPLAY_NAMES = [
    'basics' => 'Basics',
    'types' => 'Types & Error Handling',
    'async' => 'Async',
    'collections' => 'Collections',
    'text' => 'Text & Encoding',
    'io' => 'I/O',
    'networking' => 'Networking',
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

    $sourceBaseUrl = 'https://github.com/azjezz/psl/tree/' . $gitRef . '/';

    $docs = [];
    $categories = [];
    foreach (CATEGORY_DISPLAY_NAMES as $dirName => $displayName) {
        $categorySlugs = [];
        foreach (scan_for_files(CONTENT_DIR . '/' . $dirName, '.md') as $file) {
            $slug = Filesystem\get_basename($file, '.md');
            $content = File\read($file);
            $content = namespace\process_markdown($content);
            $content = Regex\replace($content, '/`(src\/Psl\/[^`]*)`/', '[`$1`](' . $sourceBaseUrl . '$1)');
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

    $css = File\read(RESOURCES_DIR . '/style.css');
    $js = File\read(RESOURCES_DIR . '/app.js');
    $template = File\read(RESOURCES_DIR . '/template.html');

    $html = Str\replace($template, '{{CSS}}', $css);
    $html = Str\replace($html, '{{VERSION}}', $gitRef);
    $html = Str\replace($html, '{{DOCS}}', $docsJson);
    $html = Str\replace($html, '{{CATEGORIES}}', $categoriesJson);
    $html = Str\replace($html, '{{TITLES}}', $titlesJson);
    $html = Str\replace($html, '{{JS}}', $js);

    if (!Filesystem\is_directory(OUTPUT_DIR)) {
        Filesystem\create_directory(OUTPUT_DIR);
    }

    File\write(OUTPUT_FILE, $html, File\WriteMode::Truncate);

    IO\write_line('Generated %s (%d components)', OUTPUT_FILE, Iter\count($docs));

    return 0;
});
