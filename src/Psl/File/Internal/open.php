<?php

declare(strict_types=1);

namespace Psl\File\Internal;

use Psl\File\ReadHandleInterface;
use Psl\File\WriteHandleInterface;
use Psl\IO;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
function open(string $filename, string $mode, bool $read, bool $write): ReadHandleInterface&WriteHandleInterface
{
    $resource = IO\Internal\open_resource($filename, $mode);

    return new ResourceHandle($filename, $resource, $read, $write);
}
