# Channel

The `Channel` component provides message-passing channels for async communication, inspired by Go's channels and Rust's `std::sync::mpsc`. A channel splits into a `SenderInterface` and a `ReceiverInterface`, giving you a clear separation between the producing and consuming sides.

Channels come in two flavors:

- **Bounded** -- Has a fixed capacity. Sending blocks when the channel is full.
- **Unbounded** -- Grows without limit. Sending never blocks due to capacity.

## Usage

### Creating Channels

Both factory functions return a `[ReceiverInterface, SenderInterface]` pair:

@example('async/channel-basic.php')

### Sending and Receiving

@example('async/channel-send-receive.php')

`send()` waits for space if the channel is full. `receive()` waits for a message if the channel is empty. For non-blocking alternatives, use `trySend()` and `tryReceive()`, which throw immediately instead of waiting:

@example('async/channel-try-send-receive.php')

### Closing Channels

Closing a channel signals that no more messages will be sent. Messages already in the channel can still be received:

@example('async/channel-closing.php')

### Inspecting Channel State

@example('async/channel-inspecting.php')

For unbounded channels, `getCapacity()` returns `null` and `isFull()` always returns `false`.

### Producer-Consumer Pattern

@example('async/channel-producer-consumer.php')

### Fan-Out: Multiple Consumers

Distribute work across several consumers by sharing the receiver:

@example('async/channel-fan-out.php')

### Cancellation

Both `send()` and `receive()` accept an optional `CancellationTokenInterface`. This is useful when you don't want to wait indefinitely for a message or for space in a bounded channel:

@example('async/channel-cancellation.php')

`trySend()` and `tryReceive()` are non-blocking and don't need cancellation tokens -- they throw immediately if the channel is full or empty.

## When to Use Channel

- **Decoupling producers and consumers** -- The sender doesn't need to know who processes the messages
- **Backpressure** -- Bounded channels naturally slow down fast producers when consumers can't keep up
- **Async coordination** -- Pass data between concurrent tasks without shared mutable state

See `src/Psl/Channel/` for the full API.
