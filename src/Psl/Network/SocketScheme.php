<?php

declare(strict_types=1);

namespace Psl\Network;

/**
 * A socket scheme.
 */
enum SocketScheme: string
{
    case Tcp = 'tcp';
    case Unix = 'unix';
}
