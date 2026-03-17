<?php

declare(strict_types=1);

namespace Psl\URL\Internal;

/**
 * Get the default port for a known URI scheme.
 *
 * Returns null for unknown schemes.
 *
 * @return null|int<0, 65535>
 *
 * @internal
 */
function default_port(string $scheme): null|int
{
    return match ($scheme) {
        'http', 'ws' => 80,
        'https', 'wss' => 443,
        'ftp' => 21,
        'ftps' => 990,
        'ssh', 'sftp' => 22,
        'ldap' => 389,
        'ldaps' => 636,
        'redis' => 6_379,
        'rediss' => 6_380,
        'mysql' => 3_306,
        'postgres' => 5_432,
        'amqp' => 5_672,
        'amqps' => 5_671,
        'mqtt' => 1_883,
        'mqtts' => 8_883,
        'git' => 9_418,
        'telnet' => 23,
        'dns' => 53,
        default => null,
    };
}
