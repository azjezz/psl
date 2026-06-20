<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Option;

$username = 'Alice';

$greeting = Option\from_nullable::<string>($username)->proceed::<string>(
    fn(string $name) => "Welcome back, {$name}!",
    fn() => 'Welcome, guest!',
);
