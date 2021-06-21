<?php

declare(strict_types=1);

namespace Psl\Filesystem\Internal;

use Psl;
use Psl\Filesystem\ReadWriteFileHandleInterface;
use Psl\Internal;

use function error_get_last;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
function open_file(string $filename, string $mode): ReadWriteFileHandleInterface
{
    return Internal\suppress(static function () use ($filename, $mode) {
        $resource = fopen($filename, $mode);
        if ($resource === false) {
            $error = error_get_last();
            $message = $error['message'] ?? 'Unable to open resource.';
            Psl\invariant_violation($message);
        }

        return new ResourceFileHandle($filename, $resource);
    });
}
