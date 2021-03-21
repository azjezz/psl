<?php

use Psl\Asio;
use Psl\Asio\Internal;

require_once __DIR__ . '/../vendor/autoload.php';

$result = Asio\async(function () {
  $input = STDIN;
  stream_set_blocking($input, false);

  $content = fread($input, 10);
  echo "read 10 bytes from STDIN: " . var_export($content, true) . "\n\n";

  echo "awaiting for the stream to be readable ( type more than 10 bytes - please ).\n\n";

  $result = Asio\await(Internal\stream_await($input, Internal\STREAM_AWAIT_READ));
  assert($result === Internal\STREAM_AWAIT_READY, 'stream is not ready for reading.');

  echo "stream became readable.\n\n";
  $content = fread($input, 10);
  echo "read 10 bytes from STDIN: " . var_export($content, true) . "\n\n";


  echo "awaiting for the stream to be readable ( do not type ).\n\n";

  $result = Asio\await(Internal\stream_await($input, Internal\STREAM_AWAIT_READ, 10));
  assert($result === Internal\STREAM_AWAIT_READY, 'stream is not ready for reading.');
  echo "returned immediately as expected.\n\n";

  $content = fread($input, 10);
  echo "read 10 bytes from STDIN: " . var_export($content, true) . "\n\n";
});

Asio\await($result);

echo "finished.";
