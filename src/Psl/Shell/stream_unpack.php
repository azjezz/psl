<?php

declare(strict_types=1);

namespace Psl\Shell;

use Generator;
use Psl\Str;

use function unpack as byte_unpack;

/**
 * Stream unpack the result of `Shell\execute()` when using `ErrorOutputBehavior::Packed` error output behavior,
 * maintaining the outputting order, chunk by chunk.
 *
 * @param string $content
 *
 * @throws Exception\InvalidArgumentException If $content is invalid.
 *
 * @return Generator<1|2, string, void, void> Generator where the key is either 1 ( representing the standard output ),
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

        if ($type === 1 || $type === 2) {
            yield $type => $chunk;
        } else {
            throw new Exception\InvalidArgumentException('$content contains an invalid header value.');
        }
    }
}
