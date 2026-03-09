<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Class;
use Psl\IO;

IO\write_line('stdClass is final: %s', Class\is_final(stdClass::class) ? 'yes' : 'no');
IO\write_line('stdClass is abstract: %s', Class\is_abstract(stdClass::class) ? 'yes' : 'no');
IO\write_line('stdClass is readonly: %s', Class\is_readonly(stdClass::class) ? 'yes' : 'no');
