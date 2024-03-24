<?php

declare(strict_types=1);

namespace Psl\IO\Internal;

use Psl\IO\Exception;

use function error_get_last;
use function fclose;

/**
 * ` * Closes a given resource stream.
 *
 * @param resource $stream
 *
 * @internal
 *
 * @codeCoverageIgnore
 *
 * @throws Exception\RuntimeException If closing the stream fails.
 */
function close_resource(mixed $stream): void
{
    $result = @fclose($stream);
    if ($result === false) {
        /** @var array{message: string} $error */
        $error = error_get_last();

        throw new Exception\RuntimeException($error['message'] ?? 'unknown error.');
    }
}
