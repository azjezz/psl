<?php

declare(strict_types=1);

namespace Psl\Unix;

use Psl\Network;
use Psl\OS;

final class Server extends Network\Internal\AbstractStreamServer
{
    /**
     * Create a bound and listening instance.
     *
     * @param non-empty-string $file
     *
     * @throws Network\Exception\RuntimeException In case failed to listen to on given address.
     */
    public static function create(string $file): self
    {
        // @codeCoverageIgnoreStart
        if (OS\is_windows()) {
            throw new Network\Exception\RuntimeException('Unix server is not supported on Windows platform.');
        }
        // @codeCoverageIgnoreEnd

        $socket = Network\Internal\server_listen("unix://{$file}");

        return new self($socket);
    }
}
