<?php

declare(strict_types=1);

use Psl\Env;
use Psl\File;
use Psl\Filesystem;
use Psl\Internal\Loader;
use Psl\Iter;
use Psl\Regex;
use Psl\Shell;
use Psl\Str;
use Psl\Type;
use Psl\Vec;

require_once __DIR__ . "/../vendor/autoload.php";

(static function (array $args) {
    $command = Str\lowercase($args[1] ?? 'regenerate');
    if ('regenerate' === $command) {
        regenerate_documentation();

        exit(0);
    }

    if ('check' === $command) {
        check_documentation_diff();
    }

    echo Str\format(
        'Invalid argument: "%s", supported commands: "%s".',
        $args[1],
        Str\join(['regenerate', 'check'], '", "')
    );

    exit(-1);
})(Env\args());

/**
 * @return no-return
 */
function check_documentation_diff(): void
{
    regenerate_documentation();

    $diff = Shell\execute('git', ['diff', '--color', '--', 'docs/'], Filesystem\canonicalize(__DIR__ . '/..'));
    if ($diff !== '') {
        echo $diff;

        exit(1);
    }

    exit(0);
}

/**
 * Return documentation for all namespaces.
 */
function regenerate_documentation(): void
{
    $components_documentation = '';
    $components = get_all_components();
    foreach ($components as $component) {
        $components_documentation .= Str\format('- [%s](./component/%s.md)%s', $component, to_filename($component), "\n");
    }

    $readme_template = File\read(__DIR__ . '/templates/README.template.md');
    $readme = Str\replace($readme_template, '{{ list }}', $components_documentation);
    $mode = Filesystem\is_file($readme) ? File\WriteMode::TRUNCATE : File\WriteMode::OPEN_OR_CREATE;
    File\write(__DIR__ . '/README.md', $readme, $mode);

    foreach ($components as $component) {
        document_component($component, './../README.md');
    }
}

/**
 * Document the given component.
 */
function document_component(string $component, string $index_link): void
{
    $lines = [];
    $lines[] = Str\format('### `%s` Component', $component);
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

                $symbol_file_contents = File\read(Filesystem\canonicalize(__DIR__ . '/' . $symbol_file));
                $deprecation_notice = '';
                if (Str\contains($symbol_file_contents, '@deprecated')) {
                    $deprecation_notice .= ' ( deprecated )';
                }

                $definition_line = get_symbol_definition_line($symbol, $type);
                $lines[] = Str\format('- [%s](./.%s#L%d)%s', $symbol_short_name, $symbol_file, $definition_line, $deprecation_notice);
            }

            $lines[] = '';
        }

        return $lines;
    };

    $directory = './../src/' . Str\replace($component, '\\', '/');
    $symbols = get_component_members($component);

    $template = File\read(__DIR__ . '/templates/component.template.md');
    $current_link = Str\format('%s.md', to_filename($component));
    $current_filename = Str\format('%s/component/%s', __DIR__, $current_link);

    $documentation = Str\replace_every($template, [
        '{{ index }}' => $index_link,
        '{{ api }}' => Str\join(Vec\concat(
            $lines,
            $generator($directory, $symbols, Loader::TYPE_CONSTANTS),
            $generator($directory, $symbols, Loader::TYPE_FUNCTION),
            $generator($directory, $symbols, Loader::TYPE_INTERFACE),
            $generator($directory, $symbols, Loader::TYPE_CLASS),
            $generator($directory, $symbols, Loader::TYPE_TRAIT),
            $generator($directory, $symbols, Loader::TYPE_ENUM),
            ['']
        ), "\n"),
    ]);

    $mode = Filesystem\is_file($current_filename) ? File\WriteMode::TRUNCATE : File\WriteMode::OPEN_OR_CREATE;
    File\write($current_filename, $documentation, $mode);
}

/**
 * Return a shape contains all direct members in the given component.
 *
 * @return array<int, list<string>>
 */
function get_component_members(string $component): array
{
    /** @var (callable(list<string>): list<string>) $filter */
    $filter = static fn(array $list) => Vec\sort(Vec\filter(
        Vec\keys($list),
        static function (string $member) use ($component): bool {

            if (!Str\starts_with_ci($member, $component . '\\')) {
                return false;
            }

            $short_member_name = Type\string()->assert(Str\after_ci($member, $component . '\\'));

            return !Str\contains($short_member_name, '\\');
        }
    ));

    return [
        Loader::TYPE_CONSTANTS => $filter(Loader::CONSTANTS),
        Loader::TYPE_FUNCTION => $filter(Loader::FUNCTIONS),
        Loader::TYPE_INTERFACE => $filter(Loader::INTERFACES),
        Loader::TYPE_CLASS => $filter(Loader::CLASSES),
        Loader::TYPE_TRAIT => $filter(Loader::TRAITS),
        Loader::TYPE_ENUM => $filter(Loader::ENUMS),
    ];
}

/**
 * @return list<string>
 */
function get_all_components(): array
{
    $components = [
        'Psl',
        'Psl\\Async',
        'Psl\\Channel',
        'Psl\\Class',
        'Psl\\Collection',
        'Psl\\DataStructure',
        'Psl\\Dict',
        'Psl\\Encoding\\Base64',
        'Psl\\Encoding\\Hex',
        'Psl\\Env',
        'Psl\\File',
        'Psl\\Filesystem',
        'Psl\\Fun',
        'Psl\\Hash',
        'Psl\\Html',
        'Psl\\Interface',
        'Psl\\IO',
        'Psl\\Iter',
        'Psl\\Json',
        'Psl\\Math',
        'Psl\\Network',
        'Psl\\Observer',
        'Psl\\OS',
        'Psl\\Password',
        'Psl\\Promise',
        'Psl\\PseudoRandom',
        'Psl\\RandomSequence',
        'Psl\\Regex',
        'Psl\\Result',
        'Psl\\Runtime',
        'Psl\\SecureRandom',
        'Psl\\Shell',
        'Psl\\Str',
        'Psl\\Str\\Byte',
        'Psl\\Str\\Grapheme',
        'Psl\\TCP',
        'Psl\\Trait',
        'Psl\\Type',
        'Psl\\Unix',
        'Psl\\Vec',
    ];

    return Vec\sort($components);
}

/**
 * Return the name of the symbol type.
 */
function get_symbol_type_name(int $type): string
{
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
        case Loader::TYPE_ENUM:
            return 'Enums';
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
    } elseif (Loader::TYPE_ENUM === $type) {
        $reflection = new ReflectionEnum($symbol);
    } else {
        $reflection = new ReflectionClass($symbol);
    }

    return $reflection->getStartLine();
}

/**
 * Convert the given namespace to a filename.
 *
 * Example:
 *      to_filename('Psl\SecureRandom')
 *      => Str('secure-random')
 *
 *      to_filename('Psl\Str\Byte')
 *      => Str('str-byte')
 */
function to_filename(string $namespace): string
{
    return Str\lowercase(Regex\replace(
        Regex\replace(
            Str\replace(Str\strip_prefix($namespace, 'Psl\\'), '\\', '-'),
            '/(\p{Lu}+)(\p{Lu}\p{Ll})/u',
            '\1-\2',
        ),
        '/([\p{Ll}0-9])(\p{Lu})/u',
        '\1-\2',
    ));
}
