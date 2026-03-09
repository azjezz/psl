<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Default;

final class Example implements Default\DefaultInterface
{
    public static function default(): static
    {
        // Return an instance with default configuration.
        return new self();
    }
}

$example = Example::default();
