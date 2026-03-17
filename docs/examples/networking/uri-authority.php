<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\URI;

$uri = URI\parse('ssh://git@[::1%25eth0]:22/repo.git');

$authority = $uri->authority;

Psl\invariant($authority !== null, 'Invalid URI authority.');

// "git"
IO\write_line('%s', $authority->userInfo ?? '<unknown>');
// 22
IO\write_line('%d', $authority->port ?? 0);
// "[::1%25eth0]"
IO\write_line('%s', $authority->host->toString());
