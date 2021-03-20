<?php

use Psl\Asio;

require_once __DIR__ . '/../vendor/autoload.php';

$result = Asio\async(function () {
  $input = STDIN;
  stream_set_blocking($input, false);

  $content = fread($input, 10);
  echo "read 10 bytes from STDIN: " . var_export($content, true) . "\n\n";

  echo "awaiting for the stream to be readable ( don't type anything ).\n\n";

  $result = Asio\await(Asio\Internal\stream_await_read($input, 200));
  assert($result === Asio\Internal\STREAM_AWAIT_TIMEOUT, 'operation did not time out as expected!');

  echo "timed-out before the stream became readable.\n\n";
});

$result = Asio\await($result);

echo "finished: " .  var_export($result, true) . "\n";
