<?php

function foo() {
    return Psl\Channel\Exception\ClosedChannelException::forSending();
}

function bar() {
    throw foo();
}

function baz() {
    bar();
}

baz();
