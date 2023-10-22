<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;

use function Pest\Laravel\artisan;

it('start a Chrome Driver server', function () {
    Process::fake();

    artisan('manage:driver', ['action' => 'start'])
        ->expectsOutputToContain('Stating Google Chrome Driver on port [9515]')
        ->expectsOutputToContain('Google Chrome Driver server is up and running')
        ->assertSuccessful();

    Process::assertRan('./chromedriver --log-level=ALL --port=9515 &');
});

it('stop a Chrome Driver server', function () {
    Process::fake([
        'ps aux *' => '10101',
        '*' => Process::result(),
    ]);

    artisan('manage:driver', ['action' => 'stop'])
        ->expectsOutputToContain('Stopping Google Chrome Driver on port [9515]')
        ->expectsOutputToContain('Google Chrome Driver server stopped')
        ->doesntExpectOutputToContain("There's no server to stop on port [9515]")
        ->assertSuccessful();

    Process::assertRan("ps aux | grep '[c]hromedriver --log-level=ALL --port=9515' | awk '{print $2}'");

    Process::assertRan('kill -9 10101');
});

it('restart a Chrome Driver server', function () {
    Process::fake([
        'ps aux *' => '10101',
        '*' => Process::result(),
    ]);

    artisan('manage:driver', ['action' => 'restart'])
        ->expectsOutputToContain('Restarting Google Chrome Driver on port [9515]')
        ->expectsOutputToContain('Google Chrome Driver server restarted')
        ->doesntExpectOutputToContain("There's no server to restart on port [9515]")
        ->assertSuccessful();

    Process::assertRan("ps aux | grep '[c]hromedriver --log-level=ALL --port=9515' | awk '{print $2}'");

    Process::assertRan('kill -9 10101');

    Process::assertRan('./chromedriver --log-level=ALL --port=9515 &');
});

test('status of Chrome Driver server', function () {
    Process::fake([
        '*' => Process::result('10101'),
    ]);

    Http::fake([
        '*' => Http::response(['value' => [
            'ready' => true,
        ]], headers: ['Content-Type' => 'application/json']),
    ]);

    artisan('manage:driver', ['action' => 'status'])
        ->expectsOutputToContain('Getting Google Chrome Driver status on port [9515]')
        ->expectsOutputToContain('Google Chrome server status: [OK]')
        ->doesntExpectOutputToContain("There's no server available on port [9515]")
        ->assertSuccessful();
});

it('can\'t start a new Chrome Driver server if there\'s one already started', function () {
    Process::fake([
        '*' => Process::result('10101'),
    ]);

    artisan('manage:driver', ['action' => 'start'])
        ->expectsOutputToContain("[PID: 10101]: There's a server running already on port [9515]")
        ->doesntExpectOutput('Stating Google Chrome Driver on port [9515]')
        ->assertFailed();
});

it('can\'t stop a Chrome Driver server if there\'s no server already started', function () {
    Process::fake([]);

    artisan('manage:driver', ['action' => 'stop'])
        ->expectsOutputToContain("There's no server to stop")
        ->assertFailed();
});

it('can\'t restart a Chrome Driver server if there\'s no server already started', function () {
    Process::fake([]);

    artisan('manage:driver', ['action' => 'stop'])
        ->expectsOutputToContain("There's no server to stop on port [9515]")
        ->assertFailed();
});

it('can\'t get the status of Chrome Driver server if there\'s no server already started', function () {
    Process::fake([]);

    artisan('manage:driver', ['action' => 'restart'])
        ->expectsOutputToContain("There's no server to restart on port [9515]")
        ->assertFailed();
});

it('start 4 Chrome Driver servers', function () {

})->todo();

it('stop all the available Chrome Driver servers', function () {

})->todo();

it('restart all the available Chrome Driver servers', function () {

})->todo();

it('get the status of all the available Chrome Driver servers', function () {

})->todo();

it('list all the available Chrome Driver servers', function () {

})->todo();
