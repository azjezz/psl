<?php

declare(strict_types=1);

namespace Psl\Network;

/**
 * A socket scheme.
 *
 * @api
 */
enum SocketScheme: string
{
    case Tcp = 'tcp';
    case Udp = 'udp';
    case Unix = 'unix';
}
