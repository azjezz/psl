<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DateTime;
use Psl\Terminal;

final class AppState {}

$app = Terminal\Application::create::<AppState>(
    state: new AppState(),
    title: 'My App',
    tickInterval: DateTime\Duration::milliseconds(16), // ~60 ticks/s (default: 16ms)
    scrollSmoothing: true, // filter trackpad scroll micro-reversals (default: true)
    mouseMotion: false, // track mouse movement, not just clicks (default: false)
);
