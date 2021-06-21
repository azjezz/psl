<?php

use Psl\Asio;
use Psl\Asio\Internal;

require_once __DIR__ . '/../vendor/autoload.php';

$result = Asio\async(function () {
  $input = STDIN;
  stream_set_blocking($input, false);

  $content = fread($input, 10);
  echo "read 10 bytes from STDIN: " . var_export($content, true) . "\n\n";

  echo "awaiting for the stream to be readable ( don't type anything ).\n\n";

  $result = Asio\await(Internal\stream_await($input, Internal\STREAM_AWAIT_READ, 200));
  assert($result === Internal\STREAM_AWAIT_TIMEOUT, 'operation did not time out as expected!');

  echo "timed-out before the stream became readable ( expected ).\n\n";
});

Asio\await($result);

echo "finished.";
