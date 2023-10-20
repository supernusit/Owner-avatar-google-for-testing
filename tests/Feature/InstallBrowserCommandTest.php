<?php

use App\Facades\GoogleForTesting;
use App\GoogleDownloadable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use function Pest\Laravel\artisan;

it('download the latest browser version', function () {
    Http::fake();

    $google = GoogleForTesting::partialMock();
    $downloadable = Mockery::mock(GoogleDownloadable::class);

    $downloadable->shouldReceive('getVersion')
        ->andReturn('200.0.0.0');

    $downloadable->shouldReceive('getChromeBrowserURL')
        ->andReturn('https://edgedl.me.gvt1.com/edgedl/chrome/chrome-for-testing/200.0.0.0/linux64/chrome-linux64.zip');

    $google->shouldReceive('getLatestVersion')
        ->andReturn($downloadable);

    artisan('install:browser --latest')
        ->doesntExpectOutputToContain("There' no versions available for [200.0.0.0]")
        ->expectsOutputToContain('Downloading Google Chrome Browser [200.0.0.0]')
        ->expectsOutputToContain('Google Chrome Browser [200.0.0.0] downloaded')
        ->assertSuccessful();
});

it('it download the browser version [113.0.5672.0]', function () {
    Http::fake();

    $google = GoogleForTesting::partialMock();
    $downloadable = Mockery::mock(GoogleDownloadable::class);

    $downloadable->shouldReceive('getVersion')
        ->andReturn('113.0.5672.0');

    $downloadable->shouldReceive('getChromeBrowserURL')
        ->andReturn('https://edgedl.me.gvt1.com/edgedl/chrome/chrome-for-testing/113.0.5672.0/linux64/chrome-linux64.zip');

    $google->shouldReceive('getVersion')
        ->andReturn($downloadable);

    artisan('install:browser --ver=113.0.5672.0')
        ->doesntExpectOutputToContain("There' no versions available for [113.0.5672.0]")
        ->expectsOutputToContain('Downloading Google Chrome Browser [113.0.5672.0]')
        ->expectsOutputToContain('Google Chrome Browser [113.0.5672.0] downloaded')
        ->assertSuccessful();
});

it('download the browser on other path', function () {

})->todo();

it('try to download a pre-existing browser version', function () {

})->todo();

it('try to download a non existing browser version', function () {

})->todo();
