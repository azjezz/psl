<?php

declare(strict_types=1);

use Psl\Env;
use Psl\Filesystem;
use Psl\Internal\Loader;
use Psl\Iter;
use Psl\Str;
use Psl\Type;
use Psl\Vec;

require_once __DIR__ . "/../src/bootstrap.php";

(static function (array $args) {
    $documentation_file = __DIR__ . "/README.md";
    $template = Filesystem\read_file(__DIR__ . '/README.template.md');
    $documentation = Str\replace($template, '{{ api }}', generate_documentation());

    $command = $args[1] ?? 'regenerate';
    if ('regenerate' === $command) {
        Filesystem\write_file($documentation_file, $documentation);

        exit(0);
    }

    if ('check' === $command) {
        check_if_documentation_is_up_to_date($documentation_file, $documentation);
    }

    echo Str\format(
        'Invalid argument: "%s", supported commands: "%s".',
        $args[1],
        Str\join(['regenerate', 'check'], '", "')
    );

    exit(-1);
})(Env\args());

/**
 * Check if $documentation_file contains $expected_content, line-by-line.
 *
 * @return no-return
 */
function check_if_documentation_is_up_to_date($documentation_file, $expected_content): void
{
    $expected_lines = Str\split($expected_content, "\n");
    $actual_content = Filesystem\read_file($documentation_file);
    $actual_lines = Str\split($actual_content, "\n");

    if (Iter\count($expected_lines) !== Iter\count($actual_lines)) {
        echo "Documentation is out of date, please regenerate by running 'php docs/documenter.php'.\n\n";
        echo "Number of lines don't match.\n";
        exit(-1);
    }

    for ($i = 0, $count = count($expected_lines); $i < $count; ++$i) {
        if (Str\trim($expected_lines[$i]) !== Str\trim($actual_lines[$i])) {
            echo "Documentation is out of date, please regenerate by running 'php docs/documenter.php'.\n\n";
            echo "Difference on line $i:\n";
            echo "Expected  : " . $expected_lines[$i] . "\n";
            echo "Actual    : " . $actual_lines[$i] . "\n";
            exit(-1);
        }
    }

    echo "Documentation is up to date.\n";
    exit(0);
}

/**
 * Return documentation for all namespaces.
 */
function generate_documentation(): string
{
    $lines = [];
    $lines[] = '';

    foreach (get_all_namespaces() as $namespace) {
        $lines[] = get_namespace_documentation($namespace);
    }

    $lines[] = '';
    $lines[] = '';
    $lines[] = '---';
    $lines[] = '> This markdown file was generated using `docs/regenerate.php`. Any edits to it will likely be lost.';

    return Str\join($lines, "\n");
}

/**
 * Return documentation for the given namespace.
 */
function get_namespace_documentation(string $namespace): string
{
    $lines = [];
    $lines[] = Str\format('### `%s`', $namespace);
    $lines[] = '';

    /**
     * @param array{
     *  'constants' => list<string>,
     *  'functions' => list<string>,
     *  'interface' => list<string>,
     *  'classes' => list<string>,
     *  'traits' => list<string>,
     * } $symbols
     * @param 'constants'|'functions'|'interfaces'|'classes'|'traits' $type
     *
     * @return list<string>
     */
    $generator = static function (
        string $directory,
        array $symbols,
        string $type
    ): array {
        $lines = [];
        if (Iter\count($symbols[$type]) > 0) {
            $lines[] = Str\format('#### `%s`', Str\uppercase($type));
            $lines[] = '';

            foreach ($symbols[$type] as $function) {
                $short_name = Str\after_last($function, '\\');
                if ('constants' === $type) {
                    $url = Str\format('%s%s%s.php', $directory, Filesystem\SEPARATOR, 'constants');
                } else {
                    $url = Str\format('%s%s%s.php', $directory, Filesystem\SEPARATOR, $short_name);
                }

                $deprecation_notice = '';
                if (Str\contains(Filesystem\read_file(Filesystem\canonicalize(__DIR__ . '/' . $url)), '@deprecated')) {
                    $deprecation_notice .= ' ( deprecated )';
                }

                $lines[] = Str\format('- [%s](%s)%s', $short_name, $url, $deprecation_notice);
            }

            $lines[] = '';
        }

        return $lines;
    };

    $directory = './../src/' . Str\replace($namespace, '\\', '/');
    $symbols = get_direct_namespace_symbols($namespace);

    return Str\join(Vec\concat(
        $lines,
        $generator($directory, $symbols, 'constants'),
        $generator($directory, $symbols, 'functions'),
        $generator($directory, $symbols, 'interfaces'),
        $generator($directory, $symbols, 'classes'),
        $generator($directory, $symbols, 'traits'),
        ['']
    ), "\n");
}

/**
 * Return a shape contains all direct symbols in the given namespace.
 *
 * @return array{
 *                'constants' => list<string>,
 *                'functions' => list<string>,
 *                'interface' => list<string>,
 *                'classes' => list<string>,
 *                'traits' => list<string>,
 *                }
 */
function get_direct_namespace_symbols(string $namespace): array
{
    /** @var (callable(list<string>): list<string>) $filter */
    $filter = static fn(array $list) => Vec\filter(
        $list,
        static function (string $symbol) use ($namespace): bool {

            if (!Str\starts_with_ci($symbol, $namespace)) {
                return false;
            }

            $short_symbol_name = Type\string()->assert(Str\after_ci($symbol, $namespace . '\\'));

            return !Str\contains($short_symbol_name, '\\');
        }
    );

    return [
        'constants' => $filter(Loader::CONSTANTS),
        'functions' => $filter(Loader::FUNCTIONS),
        'interfaces' => $filter(Loader::INTERFACES),
        'classes' => $filter(Loader::CLASSES),
        'traits' => $filter(Loader::TRAITS),
    ];
}

/**
 * @return list<string>
 */
function get_all_namespaces(): array
{
    return [
        'Psl',
        'Psl\Arr',
        'Psl\Collection',
        'Psl\DataStructure',
        'Psl\Dict',
        'Psl\Encoding\Base64',
        'Psl\Encoding\Hex',
        'Psl\Env',
        'Psl\Filesystem',
        'Psl\Fun',
        'Psl\Hash',
        'Psl\Html',
        'Psl\Iter',
        'Psl\Json',
        'Psl\Math',
        'Psl\Observer',
        'Psl\Password',
        'Psl\PseudoRandom',
        'Psl\Regex',
        'Psl\Result',
        'Psl\SecureRandom',
        'Psl\Shell',
        'Psl\Str',
        'Psl\Str\Byte',
        'Psl\Str\Grapheme',
        'Psl\Type',
        'Psl\Vec',
    ];
}
