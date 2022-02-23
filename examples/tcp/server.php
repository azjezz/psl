<?php

declare(strict_types=1);

use Revolt\EventLoop;

require __DIR__ . '/../../vendor/autoload.php';

$write_line = static fn(string $m, ...$args) => printf($m . "\n", ...$args);

$write_line('Server is listening on http://localhost:3030');

// Error reporting suppressed since stream_socket_server() emits an E_WARNING on failure (checked below).
$server = @stream_socket_server('tcp://localhost:3030', $errno, $_, flags: STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, context: stream_context_create([
    'socket' => [
        'ipv6_v6only' => true,
        'so_reuseaddr' => false,
        'so_reuseport' => false,
        'so_broadcast' => false,
        'tcp_nodelay' => false,
    ]
]));
if (!$server || $errno) {
    throw new RuntimeException('Failed to listen localhost 3030.', $errno);
}

$watcher = null;
EventLoop::unreference(EventLoop::onSignal(SIGINT, static function() use ($server, &$watcher) {
    EventLoop::cancel((string) $watcher);
    fclose($server);
}));

$watcher = EventLoop::onReadable($server, static function ($watcher, $resource) {
    $stream = @stream_socket_accept($resource, timeout: 0.0);
    if (false === $stream) {
        EventLoop::cancel($watcher);

        return;
    }

    stream_set_read_buffer($stream, 0);
    stream_set_blocking($stream, false);
    $suspension = EventLoop::getSuspension();
    $watcher = EventLoop::onReadable($stream, static function() use ($suspension) {
        $suspension->resume();
    });
    $suspension->suspend();
    EventLoop::cancel($watcher);
    $request = stream_get_contents($stream);
    $suspension = EventLoop::getSuspension();
    $watcher = EventLoop::onWritable($stream, static function() use ($suspension) {
        $suspension->resume();
    });
    $suspension->suspend();
    EventLoop::cancel($watcher);
    fwrite($stream, "HTTP/1.1 200 OK\n");
    $suspension = EventLoop::getSuspension();
    $watcher = EventLoop::onWritable($stream, static function() use ($suspension) {
        $suspension->resume();
    });
    $suspension->suspend();
    EventLoop::cancel($watcher);
    fwrite($stream, "Server: TCP Server\n");
    $suspension = EventLoop::getSuspension();
    $watcher = EventLoop::onWritable($stream, static function() use ($suspension) {
        $suspension->resume();
    });
    $suspension->suspend();
    EventLoop::cancel($watcher);
    fwrite($stream, "Connection: close\n");
    $suspension = EventLoop::getSuspension();
    $watcher = EventLoop::onWritable($stream, static function() use ($suspension) {
        $suspension->resume();
    });
    $suspension->suspend();
    EventLoop::cancel($watcher);
    fwrite($stream, "Content-Type: text/html; charset=utf-8\n\n");
    $suspension = EventLoop::getSuspension();
    $watcher = EventLoop::onWritable($stream, static function() use ($suspension) {
        $suspension->resume();
    });
    $suspension->suspend();
    EventLoop::cancel($watcher);
    fwrite($stream, "<h3>Hello, World!</h3>");
    $suspension = EventLoop::getSuspension();
    $watcher = EventLoop::onWritable($stream, static function() use ($suspension) {
        $suspension->resume();
    });
    $suspension->suspend();
    EventLoop::cancel($watcher);
    fwrite($stream, "<pre><code>" . htmlentities($request) . "</code></pre>");
    $suspension = EventLoop::getSuspension();
    $watcher = EventLoop::onWritable($stream, static function() use ($suspension) {
        $suspension->resume();
    });
    $suspension->suspend();
    EventLoop::cancel($watcher);
    fwrite($stream, sprintf('memory usage: %dMiB<br />', round(memory_get_usage() / 1024 / 1024, 1)));
    $suspension = EventLoop::getSuspension();
    $watcher = EventLoop::onWritable($stream, static function() use ($suspension) {
        $suspension->resume();
    });
    $suspension->suspend();
    EventLoop::cancel($watcher);
    fwrite($stream, sprintf('peak memory usage: %dMiB<br />', round(\memory_get_peak_usage() / 1024 / 1024, 1)));
    @fclose($stream);
});

EventLoop::run();

$write_line('');
$write_line('Goodbye 👋');
