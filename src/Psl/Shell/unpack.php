<?php

declare(strict_types=1);

namespace Psl\Shell;

use Psl\Str;

use function unpack as byte_unpack;

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
    while ($content !== '') {
        if (Str\Byte\length($content) < 5) {
            throw new Exception\InvalidArgumentException('$content contains an invalid header value.');
        }

        $headers = byte_unpack('C1type/N1size', Str\Byte\slice($content, 0, 5));
        /** @var int<0, max> $type */
        $type = (int) $headers['type'];
        /** @var int<0, max> $size */
        $size = (int) $headers['size'];

        if ($size > (Str\Byte\length($content) - 5)) {
            throw new Exception\InvalidArgumentException('$content contains an invalid header value.');
        }

        $chunk = Str\Byte\slice($content, 5, $size);
        $content = Str\Byte\slice($content, $size + 5);

        if ($type === 1) {
            $result[0] .= $chunk;
        } elseif ($type === 2) {
            $result[1] .= $chunk;
        } else {
            throw new Exception\InvalidArgumentException('$content contains an invalid header value.');
        }
    }

    return $result;
}
