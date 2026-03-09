<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Type;

interface Loggable
{
    public function log(): string;
}

interface Exportable
{
    public function export(): string;
}

// Value must satisfy either type
$stringOrInt = Type\union(Type\string(), Type\int());

// Value must satisfy both types
$loggableAndExportable = Type\intersection(Type\instance_of(Loggable::class), Type\instance_of(Exportable::class));
