<?php

declare(strict_types=1);

namespace Psl\Network;

use Generator;
use Psl\IO;

/**
 * Generic interface for a class able to accept socket connections.
 *
 * Unlike {@see ServerInterface}, {@see StreamServerInterface} provides access to the underlying server stream.
 */
interface StreamServerInterface extends IO\CloseStreamHandleInterface, ServerInterface
{
    /**
     * {@inheritDoc}
     */
    public function nextConnection(): StreamSocketInterface;

    /**
     * {@inheritDoc}
     *
     * @return Generator<null, StreamSocketInterface, void, null>
     */
    public function incoming(): Generator;
}
