<?php

declare(strict_types=1);

namespace Psl\Network;

use Psl\IO;

/**
 * A handle representing a connection between processes.
 *
 * It is possible for both ends to be connected to the same process,
 * and to either be local or across a network.
 *
 * Unlike {@see SocketInterface}, {@see StreamSocketInterface} provides access to the underlying socket stream.
 */
interface StreamSocketInterface extends IO\CloseReadWriteStreamHandleInterface, SocketInterface
{
}
