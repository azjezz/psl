<?php

declare(strict_types=1);

namespace Psl\Env;

use function sys_get_temp_dir;

/**
 * Returns the value of the "TMPDIR" environment variable if it is set, otherwise it returns /tmp.
 *
 * @note On windows, we can't count on the environment variables "TEMP" or "TMP",
 *      and so must make the Win32 API call to get the default directory for temporary files.
 * @note The return value of this function can be overridden using the sys_temp_dir ini directive.
 *
 * @see https://www.php.net/manual/en/function.sys-get-temp-dir.php
 * @see https://github.com/php/php-src/blob/fd0b57d48bab3924a31d3d0b038f0d5de6eab3e3/main/php_open_temporary_file.c#L204
 *
 * @return non-empty-string
 */
function temp_dir(): string
{
    /** @var non-empty-string */
    return sys_get_temp_dir();
}
