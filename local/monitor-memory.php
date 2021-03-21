<?php

use Psl\Str;
use Psl\Asio;
use Psl\IO;
use Psl\Math;

// This is a silly script to detect memory leaks within the event loop

require_once __DIR__ . '/../vendor/autoload.php';

Asio\defer(function () {
  $peaked = 0;
  $r = fn ($m) => Math\round($m / 1048576, 2);

  $last_peak = $r(memory_get_peak_usage());
  while (true) {
    $peak = $r(memory_get_peak_usage());
    if ($peak > $last_peak) {
      $peaked++;
    }
    $last_peak = $peak;

    IO\output_handle()->writeAll(
      Str\format("\n\n[%d] - memory: %.2FmB / peak: %.2Fmb ( peaked %d times )\n\n", Asio\time(), $r(memory_get_usage()), $peak, $peaked)
    );

    Asio\await(Asio\sleep(600));
  }
});

while (true) {
  Asio\await(Asio\sleep(100));

  IO\output_handle()->writeAll('[');

  Asio\await(Asio\sleep(30));

  Asio\await(Asio\async(function () {
    Asio\await(Asio\later());
  }));

  Asio\defer(fn () =>  Asio\await(Asio\async(function () {
    IO\output_handle()->writeAll('^');
  })));

  Asio\defer(fn () => Asio\await(Asio\sleep(4000)));

  Asio\defer(fn () => IO\output_handle()->writeAll('.'));

  IO\output_handle()->writeAll('*');
  IO\output_handle()->writeAll(']');
}
