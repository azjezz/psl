<?php

declare(strict_types=1);

namespace Psl\Shell;

use Psl\Default\DefaultInterface;

/**
 * Specifies the behavior for handling the standard error (stderr) output of shell commands.
 *
 * This enum is utilized to configure how stderr output should be treated in relation to the
 * standard output (stdout) when executing shell commands via the Shell component. Each case
 * offers a different strategy for managing or combining stderr and stdout, allowing for flexible
 * error output handling based on specific requirements of the execution context.
 */
enum ErrorOutputBehavior implements DefaultInterface
{
    /**
     * Discard the standard error output.
     *
     * Example:
     *
     *      $stdout = Shell\execute('cmd', ['arg1', 'arg2'], error_output_behavior: ErrorOutputBehavior::Discard);
     */
    case Discard;

    /**
     * Append the standard error output content to the standard output content.
     *
     * Example:
     *
     *      $stdout_followed_by_stderr = Shell\execute('cmd', ['arg1', 'arg2'], error_output_behavior: ErrorOutputBehavior::Append);
     */
    case Append;

    /**
     * Prepend the standard error output content to the standard output content.
     *
     * Example:
     *
     *      $stderr_followed_by_stdout = Shell\execute('cmd', ['arg1', 'arg2'], error_output_behavior: ErrorOutputBehavior::Prepend);
     */
    case Prepend;

    /**
     * Replace the standard output content with the standard error output content.
     *
     * Example:
     *
     *      $stderr = Shell\execute('cmd', ['arg1', 'arg2'], error_output_behavior: ErrorOutputBehavior::Replace);
     */
    case Replace;

    /**
     * Pack the standard output content with the standard error output content, enabling
     * you to split them later on, using `Shell\unpack`.
     *
     * Example:
     *
     *      $result = Shell\execute('cmd', ['arg1', 'arg2'], error_output_behavior: ErrorOutputBehavior::Packed);
     *      [$stdout, $stderr] = Shell\unpack($result);
     *
     * @note The packing format is not guaranteed to be BC, you should always use `Shell\unpack` instead of attempting to unpack the result manually.
     */
    case Packed;

    /**
     * Provides the default error output behavior.
     *
     * The default behavior is to discard the standard error output. This choice simplifies
     * handling of command outputs in scenarios where error details are not crucial, or
     * errors are expected and non-critical. It offers a clean approach for focusing solely
     * on the standard output content, especially in automated scripts or where output clarity
     * is a priority.
     *
     * @return static The default `Discard` behavior instance, representing the preference to ignore stderr output.
     *
     * @pure
     */
    #[\Override]
    public static function default(): static
    {
        return self::Discard;
    }
}
