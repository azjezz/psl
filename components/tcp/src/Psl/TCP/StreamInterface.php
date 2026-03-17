<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl\Network;

/**
 * A connected TCP stream.
 *
 * Extends the base network stream interface for TCP connections.
 */
interface StreamInterface extends Network\StreamInterface {}
