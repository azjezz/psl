<?php

declare(strict_types=1);

namespace Psl\Shell;

use Generator;

use function strlen;
use function substr;
use function unpack as byte_unpack;

/**
 * Stream unpack the result of `Shell\execute()` when using `ErrorOutputBehavior::Packed` error output behavior,
 * maintaining the outputting order, chunk by chunk.
 *
 * @param string $content
 *
 * @throws Exception\InvalidArgumentException If $content is invalid.
 *
 * @return Generator<1|2, string, null, void> Generator where the key is either 1 ( representing the standard output ),
 *                                            or 2 ( representing the standard error output ), and the value is the output chunk.
 *
 * Example:
 *
 *      Shell\stream_unpack(
 *          Shell\execute('php', ['-r', 'fwrite(STDOUT, "a"); fwrite(STDERR, "b"); fwrite(STDOUT, "c");'], null, [], ErrorOutputBehavior::Packed),
 *      );
 *      => Generator(1 => "a", 2 => "b", 1 => "c")
 */
function stream_unpack(string $content): Generator
{
    while ('' !== $content) {
        if (strlen($content) < 5) {
            throw new Exception\InvalidArgumentException('$content contains an invalid header value.');
        }

        $headers = byte_unpack('C1type/N1size', substr($content, 0, 5));
        if (false === $headers) {
            throw new Exception\InvalidArgumentException('$content contains an invalid header value.');
        }

        /** @var int<0, max> $type */
        $type = (int) $headers['type'];
        /** @var int<0, max> $size */
        $size = (int) $headers['size'];

        if ($size > (strlen($content) - 5)) {
            throw new Exception\InvalidArgumentException('$content contains an invalid header value.');
        }

        $chunk = substr($content, 5, $size);
        $content = substr($content, $size + 5);

        if (1 === $type || 2 === $type) {
            yield $type => $chunk;
            continue;
        }

        throw new Exception\InvalidArgumentException('$content contains an invalid header value.');
    }
}
