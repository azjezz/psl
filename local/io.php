<?php

use Psl\Asio;

require_once __DIR__ . '/../vendor/autoload.php';

$result = Asio\async(function () {
  $input = STDIN;
  stream_set_blocking($input, false);

  $content = fread($input, 10);
  echo "read 10 bytes from STDIN: " . var_export($content, true) . "\n\n";

  echo "awaiting for the stream to be readable ( type something ).\n\n";

  $result = Asio\await(Asio\Internal\stream_await_read($input));
  assert($result === Asio\Internal\STREAM_AWAIT_READY, 'stream is not ready for reading.');

  echo "stream became readable.\n\n";
  $content = fread($input, 10);
  echo "read 10 bytes from STDIN: " . var_export($content, true) . "\n\n";
});

$result = Asio\await($result);

echo "finished: " .  var_export($result, true) . "\n";
