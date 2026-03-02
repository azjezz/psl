<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Hash;
use Psl\IO;

$chunkOne = 'Hello, ';
$chunkTwo = 'World';
$chunkThree = '!';

$digest = Hash\Context::forAlgorithm(Hash\Algorithm::Sha256)
    ->update($chunkOne)
    ->update($chunkTwo)
    ->update($chunkThree)
    ->finalize();

IO\write_line('Incremental SHA-256: %s', $digest);
