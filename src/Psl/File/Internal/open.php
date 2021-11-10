<?php

declare(strict_types=1);

namespace Psl\File\Internal;

use Psl\File\ReadWriteHandleInterface;
use Psl\IO;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
function open(string $filename, string $mode, bool $read, bool $write): ReadWriteHandleInterface
{
    $resource = IO\Internal\open_resource($filename, $mode);

    return new ResourceHandle($filename, $resource, $read, $write);
}
