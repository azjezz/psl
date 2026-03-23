<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DNS\System\HostsFile\HostsFile;
use Psl\DNS\System\Settings;

// Load system DNS settings (nameservers, search domains)
$settings = Settings::load();
foreach ($settings->nameservers as $ns) {
    $ns->host; // "8.8.8.8"
    $ns->port; // 53
    $ns->forDomains; // [] for global, ["corp.internal"] for scoped
}

$settings->searchDomains; // ["example.com", "corp.example.com"]

// Load and query the hosts file
$hosts = HostsFile::load();
$addresses = $hosts->lookup('localhost');

// [Address("127.0.0.1"), Address("::1")]
