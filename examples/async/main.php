<?php

declare(strict_types=1);

namespace Psl\Example\IO;

use Psl\Async;
use Psl\IO;
use Psl\Shell;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function(): int {
  $watcher = Async\Scheduler::onSignal(SIGINT, static function (): never {
      IO\write_line('SIGINT received, stopping...');
      exit(0);
  });

  Async\Scheduler::unreference($watcher);

  IO\write_error_line('Press Ctrl+C to stop');

  Async\concurrently([
      static fn(): string => Shell\execute('sleep', ['3']),
      static fn(): string => Shell\execute('echo', ['Hello World!']),
      static fn(): string => Shell\execute('echo', ['Hello World!']),
  ]);

  IO\write_error_line('Done!');

  return 0;
});
