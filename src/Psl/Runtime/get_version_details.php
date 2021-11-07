<?php

declare(strict_types=1);

namespace Psl\Runtime;

use const PHP_EXTRA_VERSION;
use const PHP_MAJOR_VERSION;
use const PHP_MINOR_VERSION;
use const PHP_RELEASE_VERSION;

/**
 * Return the current PHP version as an array.
 *
 * @return array{major: non-empty-string, minor: non-empty-string, release: non-empty-string, extra: non-empty-string|null}
 *
 * @pure
 */
function get_version_details(): array
{
    /** @var array{major: non-empty-string, minor: non-empty-string, release: non-empty-string, extra: non-empty-string|null} */
    return [
        'major' => PHP_MAJOR_VERSION,
        'minor' => PHP_MINOR_VERSION,
        'release' => PHP_RELEASE_VERSION,
        'extra' => PHP_EXTRA_VERSION ?: null,
    ];
}
