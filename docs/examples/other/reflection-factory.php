<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Class;
use Psl\IO;

function create(string $class): object
{
    if (!Class\exists($class)) {
        throw new InvalidArgumentException($class . ' does not exist.');
    }

    if (Class\is_abstract($class)) {
        throw new InvalidArgumentException($class . ' is abstract and cannot be instantiated.');
    }

    // @mago-expect analysis:unknown-class-instantiation
    return new $class();
}

$obj = create(stdClass::class);
IO\write_line('Created: %s', get_class($obj));
