<?php

use Clue\React\Stdio\Readline;
use Clue\React\Stdio\Stdio;
use React\EventLoop\Loop;
use Tests\FakeClient;
use Tests\FakeServer;

uses(Tests\TestCase::class)->in('Feature');

uses()
    ->beforeEach(function () {
        startConsole();
        test()->server = new FakeServer();
        test()->client = new FakeClient();
    })
    ->afterEach(function () {
        test()->server->close();
    })
    ->in('Feature');

function fixture($name)
{
    return hex2binary(file_get_contents(__DIR__.'/Feature/fixtures/'.$name.'.txt'));
}

function startConsole()
{
    $input = test()->getMockBuilder('React\Stream\ReadableStreamInterface')->getMock();
    $output = test()->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();

    test()->console = new Stdio(Loop::get(), $input, $output, new Readline($input, $output));

    $output->expects(test()->any())->method('write')->will(test()->returnCallback(function ($data) {
        test()->output .= $data;
    }));
}
