<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Encoding\Base64;
use Psl\SecureRandom;

// Standard encoding may produce URL-unfriendly characters
$binaryToken = SecureRandom\bytes(8);
$token = Base64\encode($binaryToken);

// URL-safe encoding is better for query parameters, filenames, and tokens
$token = Base64\encode($binaryToken, Base64\Variant::UrlSafe);
$original = Base64\decode($token, Base64\Variant::UrlSafe);
