<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Default\DefaultInterface;

enum TransportMethod implements DefaultInterface
{
    case Air;
    case Water;
    case Land;

    public static function default(): static
    {
        return self::Land;
    }
}

// Obtaining a default instance of TransportMethod
$transportMethod = TransportMethod::default();
