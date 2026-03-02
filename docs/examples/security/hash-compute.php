<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Hash;
use Psl\IO;

$digest = Hash\hash('Hello, World!', Hash\Algorithm::Sha256);
// 'dffd6021bb2bd5b0af676290809ec3a53191dd81c7f70a4b28688a362182986f'
IO\write_line('SHA-256: %s', $digest);

$md5 = Hash\hash('Hello, World!', Hash\Algorithm::Md5);
IO\write_line('MD5: %s', $md5);
