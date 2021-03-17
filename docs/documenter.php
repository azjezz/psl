<?php

declare(strict_types=1);

use Psl\Env;
use Psl\Filesystem;
use Psl\Internal\Loader;
use Psl\Iter;
use Psl\Regex;
use Psl\Shell;
use Psl\Str;
use Psl\Type;
use Psl\Vec;

require_once __DIR__ . "/../src/bootstrap.php";

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

    $readme_template = Filesystem\read_file(__DIR__ . '/templates/README.template.md');
    $readme = Str\replace($readme_template, '{{ list }}', $components_documentation);
    Filesystem\write_file(__DIR__ . '/README.md', $readme);

    $previous = './../README.md';
    $component = null;
    foreach ($components as $component) {
        $previous = document_component($component, $previous);
    }

    // remove [next]({{ next }}) line from the last component documentation.
    $last_filename = Str\format('%s/component/%s', __DIR__, $previous);
    $last_file_content = Filesystem\read_file($last_filename);
    Filesystem\write_file($last_filename, Str\replace($last_file_content, "\n\n---\n\n> [next]({{ next }})\n", ''));
}

/**
 * Document the given component.
 *
 * @return string The document filename relative to docs/component/
 */
function document_component(string $component, string $previous_link): string
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

                $symbol_file_contents = Filesystem\read_file(Filesystem\canonicalize(__DIR__ . '/' . $symbol_file));
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

    $template = Filesystem\read_file(__DIR__ . '/templates/component.template.md');
    $previous_filename = __DIR__ . '/component/' . $previous_link;
    $current_link = Str\format('%s.md', to_filename($component));
    if (Filesystem\exists($previous_filename)) {
        $previous_content = Filesystem\read_file($previous_filename);
        Filesystem\write_file($previous_filename, Str\replace($previous_content, '{{ next }}', $current_link));
    }

    $current_filename = Str\format('%s/component/%s', __DIR__, $current_link);

    $documentation = Str\replace_every($template, [
        '{{ previous }}' => $previous_link,
        '{{ api }}' => Str\join(Vec\concat(
            $lines,
            $generator($directory, $symbols, Loader::TYPE_CONSTANTS),
            $generator($directory, $symbols, Loader::TYPE_FUNCTION),
            $generator($directory, $symbols, Loader::TYPE_INTERFACE),
            $generator($directory, $symbols, Loader::TYPE_CLASS),
            $generator($directory, $symbols, Loader::TYPE_TRAIT),
            ['']
        ), "\n"),
    ]);

    Filesystem\write_file($current_filename, $documentation);

    return $current_link;
}

/**
 * Return a shape contains all direct members in the given component.
 *
 * @return array<int, list<string>>
 */
function get_component_members(string $component): array
{
    /** @var (callable(list<string>): list<string>) $filter */
    $filter = static fn(array $list) => Vec\filter(
        $list,
        static function (string $member) use ($component): bool {

            if (!Str\starts_with_ci($member, $component)) {
                return false;
            }

            $short_member_name = Type\string()->assert(Str\after_ci($member, $component . '\\'));

            return !Str\contains($short_member_name, '\\');
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
function get_all_components(): array
{
    $components = [
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
            '/(\p{Lu}+)(\p{Lu}\p{Ll})/u', '\1-\2',
        ),
        '/([\p{Ll}0-9])(\p{Lu})/u', '\1-\2',
    ));
}
