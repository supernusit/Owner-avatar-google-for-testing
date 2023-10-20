<?php

use App\Facades\GoogleForTesting;
use App\GoogleDownloadable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use function Pest\Laravel\artisan;

it('download default driver version', function () {
    Http::fake();

    $google = GoogleForTesting::partialMock();
    $downloadable = Mockery::mock(GoogleDownloadable::class);

    $downloadable->shouldReceive('getVersion')
        ->andReturn('115.0.5763.0');

    $downloadable->shouldReceive('download');

    $google->shouldReceive('getVersion')
        ->andReturn($downloadable);

    artisan('install:driver')
        ->doesntExpectOutputToContain("There' no versions available for [115.0.5763.0]")
        ->expectsOutputToContain('Downloading Google Chrome Driver [115.0.5763.0]')
        ->expectsOutputToContain('Google Chrome Driver [115.0.5763.0] downloaded')
        ->expectsOutputToContain('Google Chrome Driver unzip it on')
        ->assertSuccessful();
});

it('download the latest driver version', function () {
    Http::fake();

    $google = GoogleForTesting::partialMock();
    $downloadable = Mockery::mock(GoogleDownloadable::class);

    $downloadable->shouldReceive('getVersion')
        ->andReturn('200.0.0.0');

    $downloadable->shouldReceive('download');

    $downloadable->shouldReceive('getChromeBrowserURL')
        ->andReturn('https://edgedl.me.gvt1.com/edgedl/chrome/chrome-for-testing/200.0.0.0/linux64/chrome-linux64.zip');

    $google->shouldReceive('getLatestVersion')
        ->andReturn($downloadable);

    artisan('install:driver --latest')
        ->doesntExpectOutputToContain("There' no versions available for [200.0.0.0]")
        ->expectsOutputToContain('Downloading Google Chrome Driver [200.0.0.0]')
        ->expectsOutputToContain('Google Chrome Driver [200.0.0.0] downloaded')
        ->assertSuccessful();
});

it('it download the driver version [118.0.5672.0]', function () {
    Http::fake();

    $google = GoogleForTesting::partialMock();
    $downloadable = Mockery::mock(GoogleDownloadable::class);

    $downloadable->shouldReceive('getVersion')
        ->andReturn('118.0.5672.0');

    $downloadable->shouldReceive('download');

    $google->shouldReceive('getVersion')
        ->andReturn($downloadable);

    artisan('install:driver --ver=118.0.5672.0')
        ->doesntExpectOutputToContain("There' no versions available for [118.0.5672.0]")
        ->expectsOutputToContain('Downloading Google Chrome Driver [118.0.5672.0]')
        ->expectsOutputToContain('Google Chrome Driver [118.0.5672.0] downloaded')
        ->assertSuccessful();
});

it('download the driver on other path', function () {
    Http::fake();

    $google = GoogleForTesting::partialMock();
    $downloadable = Mockery::mock(GoogleDownloadable::class);

    $downloadable->shouldReceive('getVersion')
        ->andReturn('200.0.0.0');

    $downloadable->shouldReceive('download');

    $google->shouldReceive('getLatestVersion')
        ->andReturn($downloadable);

    artisan('install:driver --latest --path=/some/dir/to/download')
        ->doesntExpectOutputToContain("There' no versions available for [200.0.0.0]")
        ->expectsOutputToContain('Downloading Google Chrome Driver [200.0.0.0]')
        ->expectsOutputToContain('Google Chrome Driver [200.0.0.0] downloaded')
        ->expectsOutputToContain('Google Chrome Driver unzip it on [/some/dir/to/download]')
        ->assertSuccessful();
});

it('try to download a pre-existing driver version', function () {
    Http::fake();

    Log::partialMock()
        ->shouldReceive('error')
        ->with('The file [chromedriver-linux.zip] already exists');

    $google = GoogleForTesting::partialMock();
    $downloadable = Mockery::mock(GoogleDownloadable::class);

    $downloadable
        ->shouldReceive('getVersion')
        ->andReturn('115.0.5763.0');

    $downloadable
        ->shouldReceive('download')
        ->andThrow(\Exception::class, 'The file [chromedriver-linux.zip] already exists');

    $google->shouldReceive('getVersion')
        ->andReturn($downloadable);

    artisan('install:driver')
        ->doesntExpectOutputToContain('Google Chrome Driver [115.0.5763.0] downloaded')
        ->assertFailed();
});
