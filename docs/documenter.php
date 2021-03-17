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
     * @param array<int, list<string>> $symbols
     *
     * @return list<string>
     */
    $generator = static function (
        string $directory,
        array $symbols,
        int $type
    ): array {
        $lines = [];
        if (Iter\count($symbols[$type]) > 0) {
            $lines[] = Str\format('#### `%s`', get_symbol_type_name($type));
            $lines[] = '';

            foreach ($symbols[$type] as $symbol) {
                $symbol_short_name = Str\after_last($symbol, '\\');
                if (Loader::TYPE_CONSTANTS === $type) {
                    $symbol_file = Str\format('%s%s%s.php', $directory, Filesystem\SEPARATOR, 'constants');
                } else {
                    $symbol_file = Str\format('%s%s%s.php', $directory, Filesystem\SEPARATOR, $symbol_short_name);
                }

                $symbol_file_contents = Filesystem\read_file(Filesystem\canonicalize(__DIR__ . '/' . $symbol_file));
                $deprecation_notice = '';
                if (Str\contains($symbol_file_contents, '@deprecated')) {
                    $deprecation_notice .= ' ( deprecated )';
                }

                $definition_line = get_symbol_definition_line($symbol, $type);
                $lines[] = Str\format('- [%s](%s#L%d)%s', $symbol_short_name, $symbol_file, $definition_line, $deprecation_notice);
            }

            $lines[] = '';
        }

        return $lines;
    };

    $directory = './../src/' . Str\replace($namespace, '\\', '/');
    $symbols = get_direct_namespace_symbols($namespace);

    return Str\join(Vec\concat(
        $lines,
        $generator($directory, $symbols, Loader::TYPE_CONSTANTS),
        $generator($directory, $symbols, Loader::TYPE_FUNCTION),
        $generator($directory, $symbols, Loader::TYPE_INTERFACE),
        $generator($directory, $symbols, Loader::TYPE_CLASS),
        $generator($directory, $symbols, Loader::TYPE_TRAIT),
        ['']
    ), "\n");
}

/**
 * Return a shape contains all direct symbols in the given namespace.
 *
 * @return array<int, list<string>>
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
        Loader::TYPE_CONSTANTS => $filter(Loader::CONSTANTS),
        Loader::TYPE_FUNCTION => $filter(Loader::FUNCTIONS),
        Loader::TYPE_INTERFACE => $filter(Loader::INTERFACES),
        Loader::TYPE_CLASS => $filter(Loader::CLASSES),
        Loader::TYPE_TRAIT => $filter(Loader::TRAITS),
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

/**
 * Return the name of the symbol type.
 */
function get_symbol_type_name(int $type): string {
    switch ($type) {
        case Loader::TYPE_CONSTANTS:
            return 'Constants';
        case Loader::TYPE_FUNCTION:
            return 'Functions';
        case Loader::TYPE_INTERFACE:
            return 'Interfaces';
        case Loader::TYPE_CLASS:
            return 'Classes';
        case Loader::TYPE_TRAIT:
            return 'Traits';
    }
}

/**
 * Return the line where $symbol is defined.
 */
function get_symbol_definition_line(string $symbol, int $type): int
{
    if (Loader::TYPE_CONSTANTS === $type) {
        return 0;
    }

    if (Loader::TYPE_FUNCTION === $type) {
        $reflection = new ReflectionFunction($symbol);
    } else {
        $reflection = new ReflectionClass($symbol);
    }

    return $reflection->getStartLine();
}
