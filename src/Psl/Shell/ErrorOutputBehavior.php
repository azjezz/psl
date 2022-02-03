<?php

declare(strict_types=1);

namespace Psl\Shell;

enum ErrorOutputBehavior {
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
}
