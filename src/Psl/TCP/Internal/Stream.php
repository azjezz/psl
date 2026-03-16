<?php

declare(strict_types=1);

namespace Psl\TCP\Internal;

use Psl\Network;
use Psl\TCP;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class Stream extends Network\Internal\Stream implements TCP\StreamInterface {}
