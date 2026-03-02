<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Ansi;
use Psl\Ansi\Style;
use Psl\IO;

IO\write(Ansi\link('GitHub', 'https://github.com', Style\bold(), Style\underline()));
IO\write("\n");
