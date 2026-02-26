# Process

The `Process` component provides a typed, non-blocking API for spawning and managing child processes, built on top of PSL's IO system.

It serves as an alternative to `proc_*`, `symfony/process`, and `amphp/process`, with an API inspired by Rust's `std::process` module adapted to PSL conventions.

## Usage

```php
use Psl\Process\Command;

// Run a command and collect its output
$output = Command::create('git')
    ->withArguments(['log', '--oneline', '-n', '5'])
    ->output();

if ($output->status->isSuccessful()) {
    echo $output->stdout;
}
```

## API

### Classes

---

#### `Command`

Immutable builder for constructing and spawning child processes. All `with*()` methods return a new instance.

**Factories:**

- `Command::create(string $program): self` — Create a command that executes a program directly, bypassing the shell. This prevents shell injection and is the recommended way to run commands.
- `Command::shell(string $command): self` — Create a command interpreted by the system shell (`/bin/sh -c` on Unix, `cmd.exe` on Windows). Allows shell features like pipes, globbing, and variable expansion.

**Builder Methods:**

- `withArgument(string $argument): self` — Append a single argument.
- `withArguments(list<string> $arguments): self` — Append multiple arguments.
- `withEnvironmentVariable(string $name, string $value): self` — Set an environment variable.
- `withEnvironmentVariables(array<string, string> $variables): self` — Set multiple environment variables.
- `withoutEnvironmentVariable(string $name): self` — Remove an environment variable.
- `withClearedEnvironment(): self` — Clear all environment variables.
- `withWorkingDirectory(string $directory): self` — Set the working directory.
- `withStdin(Stdio $stdio): self` — Configure stdin (default: `Stdio::null()`).
- `withStdout(Stdio $stdio): self` — Configure stdout (default: `Stdio::piped()`).
- `withStderr(Stdio $stdio): self` — Configure stderr (default: `Stdio::piped()`).

**Getters:**

- `getProgram(): string`
- `getArguments(): list<string>`
- `getEnvironmentVariables(): array<string, string>`
- `getWorkingDirectory(): null|string`

**Entry Points:**

- `spawn(): ChildInterface` — Spawn the process using the configured stdio and return a child handle.
- `output(null|Duration $timeout = null): Output` — Spawn with piped stdout/stderr, collect all output, and wait for exit.
- `status(null|Duration $timeout = null): ExitStatus` — Spawn with null stdio and wait for exit, returning only the exit status.

---

#### `Stdio`

Describes what to do with a standard I/O stream for a child process.

- `Stdio::piped(): self` — Create a pipe between the parent and child processes. This is the default for stdout and stderr.
- `Stdio::inherit(): self` — The child inherits the corresponding parent descriptor.
- `Stdio::null(): self` — Attach the stream to `/dev/null` (or `NUL` on Windows). This is the default for stdin.
- `Stdio::tty(): self` — Connect the child directly to the terminal (`/dev/tty`). Useful for interactive programs (vim, crontab -e, git commit) or programs that detect TTY for colored output. Unix only.
- `Stdio::fromStreamHandle(IO\StreamHandleInterface $handle): self` — Use an existing stream handle.

---

#### `ExitStatus`

Represents the exit status of a completed process.

- `isSuccessful(): bool` — Whether the process exited with code 0.
- `getCode(): int` — The exit code.
- `hasBeenSignaled(): bool` — Whether the process was terminated by a signal.
- `getTerminationSignal(): null|Signal` — The signal that terminated the process, if any.

---

#### `Output`

Holds the collected output from a completed process.

**Properties:**
- `ExitStatus $status` — The exit status.
- `string $stdout` — The captured standard output.
- `string $stderr` — The captured standard error.

---

### Interfaces

---

#### `ChildInterface`

Represents a running child process. Returned by `Command::spawn()`.

- `getProcessId(): int` — Returns the OS-assigned process ID.
- `isRunning(): bool` — Whether the process is still running.
- `getStdin(): IO\WriteHandleInterface & IO\CloseHandleInterface & IO\StreamHandleInterface` — Returns the stdin handle for writing. Throws `StreamUnavailableException` if stdin was not configured as piped.
- `getStdout(): IO\ReadHandleInterface & IO\CloseHandleInterface & IO\StreamHandleInterface` — Returns the stdout handle for reading. Throws `StreamUnavailableException` if stdout was not configured as piped.
- `getStderr(): IO\ReadHandleInterface & IO\CloseHandleInterface & IO\StreamHandleInterface` — Returns the stderr handle for reading. Throws `StreamUnavailableException` if stderr was not configured as piped.
- `signal(Signal $signal): void` — Sends a signal to the process.
- `kill(): void` — Sends SIGKILL to forcefully terminate the process.
- `wait(null|Duration $timeout = null): ExitStatus` — Waits for the process to exit. Closes piped handles before waiting to prevent deadlocks.
- `waitWithOutput(null|Duration $timeout = null): Output` — Reads stdout and stderr concurrently, then waits for exit.
- `tryWait(): null|ExitStatus` — Non-blocking check: returns exit status if exited, null if still running.

---

### Enums

---

#### `Signal`

POSIX signals that can be sent to a process.

| Case | Value |
|------|-------|
| `Hangup` | 1 |
| `Interrupt` | 2 |
| `Quit` | 3 |
| `Kill` | 9 |
| `User1` | 10 |
| `User2` | 12 |
| `Alarm` | 14 |
| `Terminate` | 15 |

---

## Examples

### Collect Output

```php
use Psl\Process\Command;

$output = Command::create('php')
    ->withArguments(['-r', 'echo "hello world";'])
    ->output();

echo $output->stdout; // "hello world"
echo $output->stderr; // ""
```

### Check Exit Status

```php
use Psl\Process\Command;
use Psl\DateTime\Duration;

$status = Command::create('php')
    ->withArguments(['-r', 'exit(42);'])
    ->status();

$status->isSuccessful(); // false
$status->getCode();      // 42
```

### Write to Stdin

```php
use Psl\Process\Command;
use Psl\Process\Stdio;

$child = Command::create('cat')
    ->withStdin(Stdio::piped())
    ->spawn();

$child->getStdin()->writeAll("hello\n");
$child->getStdin()->close();

$output = $child->getStdout()->readAll();
$child->wait();
```

### Stream from a Handle

```php
use Psl\IO;
use Psl\Process\Command;
use Psl\Process\Stdio;

[$read, $write] = IO\pipe();
$write->writeAll("piped input\n");
$write->close();

$output = Command::create('cat')
    ->withStdin(Stdio::fromStreamHandle($read))
    ->output();

$read->close();

echo $output->stdout; // "piped input\n"
```

### Shell Commands

```php
use Psl\Process\Command;

$output = Command::shell('echo hello && echo world')->output();

echo $output->stdout; // "hello\nworld\n"
```

### Timeouts

```php
use Psl\Process\Command;
use Psl\Process\Exception;
use Psl\DateTime\Duration;

try {
    Command::create('sleep')
        ->withArgument('60')
        ->status(Duration::seconds(5));
} catch (Exception\TimeoutException) {
    // Process was killed after 5 seconds.
}
```

### Signals

```php
use Psl\Process\Command;
use Psl\Process\Signal;
use Psl\Process\Stdio;

$child = Command::create('sleep')
    ->withArgument('60')
    ->withStdout(Stdio::null())
    ->withStderr(Stdio::null())
    ->spawn();

$child->signal(Signal::Terminate);
$status = $child->wait();

$status->hasBeenSignaled();      // true
$status->getTerminationSignal(); // Signal::Terminate
```

### Non-Blocking Poll

```php
use Psl\Process\Command;
use Psl\Process\Stdio;

$child = Command::create('sleep')
    ->withArgument('1')
    ->withStdout(Stdio::null())
    ->withStderr(Stdio::null())
    ->spawn();

$status = $child->tryWait(); // null (still running)

// ... do other work ...

$status = $child->wait(); // blocks until done
```

### Environment Variables

```php
use Psl\Process\Command;

$output = Command::create('php')
    ->withArguments(['-r', 'echo getenv("APP_ENV");'])
    ->withEnvironmentVariable('APP_ENV', 'production')
    ->output();

echo $output->stdout; // "production"
```

---
