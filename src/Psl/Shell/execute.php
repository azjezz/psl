<?php

declare(strict_types=1);

namespace Psl\Shell;

use Psl\Dict;
use Psl\Env;
use Psl\Regex;
use Psl\SecureRandom;
use Psl\Str;
use Psl\Vec;

use function fclose;
use function is_dir;
use function is_resource;
use function proc_close;
use function proc_open;
use function stream_get_contents;

/**
 * Execute an external program.
 *
 * @param string $command The command to execute.
 * @param list<string> $arguments The command arguments listed as separate entries.
 * @param null|string $working_directory The initial working directory for the command.
 *                                       This must be an absolute directory path, or null if you want to
 *                                       use the default value ( the current directory )
 * @param array<string, string> $environment A dict with the environment variables for the command that
 *                                           will be run.
 * @param bool $escape_arguments If set to true ( default ), all $arguments will be escaped using `escape_argument`.
 *
 * @psalm-taint-sink shell $command
 *
 * @throws Exception\FailedExecutionException In case the command resulted in an exit code other than 0.
 * @throws Exception\PossibleAttackException In case the command being run is suspicious ( e.g: contains NULL byte ).
 * @throws Exception\RuntimeException In case $working_directory doesn't exist, or unable to create a new process.
 */
function execute(
    string $command,
    array $arguments = [],
    ?string $working_directory = null,
    array $environment = [],
    bool $escape_arguments = true
): string {
    if ($escape_arguments) {
        $arguments = Vec\map($arguments, static fn(string $argument): string => escape_argument($argument));
    }

    $commandline = Str\join([$command, ...$arguments], ' ');

    /** @psalm-suppress MissingThrowsDocblock - safe ( $offset is within-of-bounds ) */
    if (Str\contains($commandline, "\0")) {
        throw new Exception\PossibleAttackException('NULL byte detected.');
    }

    $environment = Dict\merge(Env\get_vars(), $environment);
    $working_directory = $working_directory ?? Env\current_dir();
    if (!is_dir($working_directory)) {
        throw new Exception\RuntimeException('$working_directory does not exist.');
    }

    $options = [];
    // @codeCoverageIgnoreStart
    if (PHP_OS_FAMILY === 'Windows') {
        $variable_cache = [];
        $variable_count = 0;
        /** @psalm-suppress MissingThrowsDocblock */
        $identifier = 'PHP_STANDARD_LIBRARY_TMP_ENV_' . SecureRandom\string(6);
        /** @psalm-suppress MissingThrowsDocblock */
        $commandline = Regex\replace_with(
            $commandline,
            '/"(?:([^"%!^]*+(?:(?:!LF!|"(?:\^[%!^])?+")[^"%!^]*+)++)|[^"]*+ )"/x',
            /**
             * @param array<array-key, string> $m
             *
             * @return string
             */
            static function (array $m) use (
                &$environment,
                &$variable_cache,
                &$variable_count,
                $identifier
            ): string {
                if (!isset($m[1])) {
                    return $m[0];
                }

                /** @var array<string, string> $variable_cache */
                if (isset($variable_cache[$m[0]])) {
                    /** @var string */
                    return $variable_cache[$m[0]];
                }

                $value = $m[1];
                if (Str\Byte\contains($value, "\0")) {
                    $value = Str\Byte\replace($value, "\0", '?');
                }

                if (false === strpbrk($value, "\"%!\n")) {
                    return '"' . $value . '"';
                }

                $value = Str\Byte\replace_every(
                    $value,
                    ['!LF!' => "\n", '"^!"' => '!', '"^%"' => '%', '"^^"' => '^', '""' => '"']
                );
                $value = '"' . Regex\replace($value, '/(\\\\*)"/', '$1$1\\"') . '"';
                /**
                 * @psalm-suppress MixedAssignment
                 * @psalm-suppress MixedOperand
                 */
                $var = $identifier . ++$variable_count;

                /**
                 * @psalm-suppress MixedArrayAssignment
                 */
                $environment[$var] = $value;

                /**
                 * @psalm-suppress MixedArrayOffset
                 * @psalm-suppress MixedArrayAssignment
                 */
                return $variable_cache[$m[0]] = '!' . $var . '!';
            },
        );

        $commandline = 'cmd /V:ON /E:ON /D /C (' . Str\Byte\replace($commandline, "\n", ' ') . ')';
        $options = [
            'bypass_shell' => true,
            'blocking_pipes' => false,
        ];
    }
    // @codeCoverageIgnoreEnd
    $descriptor = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    /** @var array<string, string> $environment */
    $process = proc_open($commandline, $descriptor, $pipes, $working_directory, $environment, $options);
    // @codeCoverageIgnoreStart
    // not sure how to replicate this, but it can happen \_o.o_/
    if (!is_resource($process)) {
        throw new Exception\RuntimeException('Failed to open a new process.');
    }
    // @codeCoverageIgnoreEnd

    $stdout_content = stream_get_contents($pipes[1]);
    $stderr_content = stream_get_contents($pipes[2]);

    fclose($pipes[1]);
    fclose($pipes[2]);

    $code = proc_close($process);
    if ($code !== 0) {
        throw new Exception\FailedExecutionException($commandline, $stdout_content, $stderr_content, $code);
    }

    return $stdout_content;
}
