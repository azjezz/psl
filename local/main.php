<?php

use Psl\Asio;

require_once __DIR__ . '/../vendor/autoload.php';

$start = Asio\time();

$a = Asio\async(function () {
  Asio\await(Asio\sleep(50));
  return 'hello';
});

$b = Asio\async(function () {
  Asio\await(Asio\sleep(100));
  return 'hey';
});

$c = Asio\async(function () {
  Asio\await(Asio\sleep(300));
  return 'hi';
});

var_dump(
  Asio\await(Asio\first([$a, $b, $c])), // should be 'hello'
  Asio\time() - $start // should be ~50
);

$handle = Asio\sleep(100);
$handle->onJoin(function () {
  echo "finished.\n";
});
$handle->onJoin(function () {
  echo "finished ( second callback ).\n";
});

echo "sleeping :)\n"; // should appear before "finished" and "finished ( second callback )"
