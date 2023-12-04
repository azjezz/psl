<?php

declare(strict_types=1);

namespace Psl\Shell;

use Psl\Dict;
use Psl\Env;
use Psl\Filesystem;
use Psl\IO;
use Psl\OS;
use Psl\Regex;
use Psl\SecureRandom;
use Psl\Str;
use Psl\Vec;

use function is_resource;
use function pack;
use function proc_close;
use function proc_open;
use function strpbrk;

/**
 * Execute an external program.
 *
 * @param non-empty-string $command The command to execute.
 * @param list<string> $arguments The command arguments listed as separate entries.
 * @param null|non-empty-string $working_directory The initial working directory for the command.
 *                                                 This must be an absolute directory path, or null if you want to
 *                                                 use the default value ( the current directory )
 * @param array<string, string> $environment A dict with the environment variables for the command that
 *                                           will be run.
 *
 * @psalm-taint-sink shell $command
 *
 * @throws Exception\FailedExecutionException In case the command resulted in an exit code other than 0.
 * @throws Exception\PossibleAttackException In case the command being run is suspicious ( e.g: contains NULL byte ).
 * @throws Exception\RuntimeException In case $working_directory doesn't exist, or unable to create a new process.
 * @throws Exception\TimeoutException If $timeout is reached before being able to read the process stream.
 */
function execute(
    string  $command,
    array   $arguments = [],
    ?string $working_directory = null,
    array   $environment = [],
    ErrorOutputBehavior $error_output_behavior = ErrorOutputBehavior::Discard,
    ?float  $timeout = null
): string {
    $arguments = Vec\map($arguments, Internal\escape_argument(...));
    $commandline = Str\join([$command, ...$arguments], ' ');

    /** @psalm-suppress MissingThrowsDocblock - safe ( $offset is within-of-bounds ) */
    if (Str\contains($commandline, "\0")) {
        throw new Exception\PossibleAttackException('NULL byte detected.');
    }

    $environment = Dict\merge(Env\get_vars(), $environment);
    $working_directory ??= Env\current_dir();
    if (!Filesystem\is_directory($working_directory)) {
        throw new Exception\RuntimeException('$working_directory does not exist.');
    }

    $options = [];
    // @codeCoverageIgnoreStart
    if (OS\is_windows()) {
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

                $var = $identifier . ((string) ++$variable_count);

                $environment[$var] = '"' . Regex\replace(
                    Str\Byte\replace_every(
                        $value,
                        ['!LF!' => "\n", '"^!"' => '!', '"^%"' => '%', '"^^"' => '^', '""' => '"']
                    ),
                    '/(\\\\*)"/',
                    '$1$1\\"',
                ) . '"';

                /** @var string */
                return $variable_cache[$m[0]] = '!' . $var . '!';
            },
        );

        $commandline = 'cmd /V:ON /E:ON /D /C (' . Str\Byte\replace($commandline, "\n", ' ') . ')';
        $options = [
            'bypass_shell' => true,
            'blocking_pipes' => false,
        ];
    } else {
        $commandline = Str\format('exec %s', $commandline);
    }
    // @codeCoverageIgnoreEnd
    $descriptor = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $process = proc_open($commandline, $descriptor, $pipes, $working_directory, $environment, $options);
    // @codeCoverageIgnoreStart
    // not sure how to replicate this, but it can happen \_o.o_/
    if (!is_resource($process)) {
        throw new Exception\RuntimeException('Failed to open a new process.');
    }
    // @codeCoverageIgnoreEnd

    $stdout = new IO\CloseReadStreamHandle($pipes[1]);
    $stderr = new IO\CloseReadStreamHandle($pipes[2]);

    try {
        $result = '';
        /** @psalm-suppress MissingThrowsDocblock */
        foreach (IO\streaming([1 => $stdout, 2 => $stderr], $timeout) as $type => $chunk) {
            if ($chunk) {
                $result .= pack('C1N1', $type, Str\Byte\length($chunk)) . $chunk;
            }
        }
    } catch (IO\Exception\TimeoutException $previous) {
        throw new Exception\TimeoutException('reached timeout while the process output is still not readable.', 0, $previous);
    } finally {
        /** @psalm-suppress MissingThrowsDocblock */
        $stdout->close();
        /** @psalm-suppress MissingThrowsDocblock */
        $stderr->close();

        $code = proc_close($process);
    }

    if ($code !== 0) {
        /** @psalm-suppress MissingThrowsDocblock */
        [$stdout_content, $stderr_content] = namespace\unpack($result);

        throw new Exception\FailedExecutionException($commandline, $stdout_content, $stderr_content, $code);
    }

    if (ErrorOutputBehavior::Packed === $error_output_behavior) {
        return $result;
    }

    /** @psalm-suppress MissingThrowsDocblock */
    [$stdout_content, $stderr_content] = namespace\unpack($result);
    return match ($error_output_behavior) {
        ErrorOutputBehavior::Prepend => $stderr_content . $stdout_content,
        ErrorOutputBehavior::Append => $stdout_content . $stderr_content,
        ErrorOutputBehavior::Replace => $stderr_content,
        ErrorOutputBehavior::Discard => $stdout_content,
    };
}
