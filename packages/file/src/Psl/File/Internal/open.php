<?php

declare(strict_types=1);

namespace Psl\File\Internal;

use Psl\IO;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
function open(string $filename, string $mode, bool $read, bool $write): ResourceHandle
{
    $resource = IO\Internal\open_resource($filename, $mode);

    return new ResourceHandle($filename, $resource, $read, $write);
}
