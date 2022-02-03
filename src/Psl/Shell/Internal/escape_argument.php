<?php

declare(strict_types=1);

namespace Psl\Shell\Internal;

use Psl\Regex;
use Psl\Str\Byte;

use const DIRECTORY_SEPARATOR;

/**
 * Escape a string to be used as a shell argument.
 *
 * @psalm-taint-escape shell
 *
 * @internal
 */
function escape_argument(string $argument): string
{
    /**
     * The following code was copied ( with modification ) from the Symfony Process Component (v5.2.3 - 2021-02-22).
     *
     * https://github.com/symfony/process/blob/b8d6eff26e48187fed15970799f4b605fa7242e4/Process.php#L1623-L1643
     *
     * @license MIT
     *
     * @see https://github.com/symfony/process/blob/b8d6eff26e48187fed15970799f4b605fa7242e4/LICENSE
     *
     * @copyright (c) 2004-2021 Fabien Potencier <fabien@symfony.com>
     */
    if ('' === $argument) {
        return '""';
    }

    if ('\\' !== DIRECTORY_SEPARATOR) {
        $argument = Byte\replace($argument, "'", "'\\''");

        return "'" . $argument . "'";
    }

    // @codeCoverageIgnoreStart
    /** @psalm-suppress MissingThrowsDocblock - safe ( $offset is within-of-bounds ) */
    if (Byte\contains($argument, "\0")) {
        $argument = Byte\replace($argument, "\0", '?');
    }

    /** @psalm-suppress MissingThrowsDocblock - safe ( $pattern is valid ) */
    if (!Regex\matches($argument, '/[\/()%!^"<>&|\s]/')) {
        return $argument;
    }

    /** @psalm-suppress MissingThrowsDocblock - safe ( $pattern is valid ) */
    $argument = Regex\replace($argument, '/(\\\\+)$/', '$1$1');
    $argument = Byte\replace_every($argument, [
        '"' => '""',
        '^' => '"^^"',
        '%' => '"^%"',
        '!' => '"^!"',
        "\n" => '!LF!'
    ]);

    return '"' . $argument . '"';
    // @codeCoverageIgnoreEnd
}
