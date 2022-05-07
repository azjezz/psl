<?php

declare(strict_types=1);

namespace Psl\Shell;

/**
 * Unpack the result of `Shell\execute()` when using `ErrorOutputBehavior::Packed` error output behavior.
 *
 * @param string $content
 *
 * @throws Exception\InvalidArgumentException If $content is invalid.
 *
 * @return array{0: string, 1: string} - A tuple, containing the standard output content as it's first element, followed by the standard error output content.
 */
function unpack(string $content): array
{
    $result = ['', ''];
    foreach (stream_unpack($content) as $type => $chunk) {
        if ($type === 1) {
            $result[0] .= $chunk;
        } else {
            $result[1] .= $chunk;
        }
    }

    return $result;
}
