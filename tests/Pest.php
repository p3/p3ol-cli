<?php

use Clue\React\Stdio\Readline;
use Clue\React\Stdio\Stdio;
use React\EventLoop\Loop;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function fixture($name)
{
    return hex2bin(file_get_contents(__DIR__.'/Feature/fixtures/'.$name.'.txt'));
}

function startConsole()
{
    $input = test()->getMockBuilder('React\Stream\ReadableStreamInterface')->getMock();
    $output = test()->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();

    test()->console = new Stdio(Loop::get(), $input, $output, new Readline($input, $output));

    $output->expects(test()->any())->method('write')->will(test()->returnCallback(function ($data) {
        test()->output = $data;
    }));
}
