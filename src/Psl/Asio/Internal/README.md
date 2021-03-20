# Amphp Event Loop

This directory contains an internal event-loop that was adopted from amphp/amp:^3.0 ( with modifications )

Currently, we cannot install `amphp/amp:^3.0` as it conflicts with `vimeo/psalm`,
we would need to wait for amphp team to release [the event-loop as a separate package](https://github.com/amphp/amp/issues/345), then we can include it as a dependency.

However, in the time being we can keep this for the sake of experimenting :)
