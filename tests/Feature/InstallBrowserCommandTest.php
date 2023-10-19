<?php

use Illuminate\Support\Facades\File;
use function Pest\Laravel\artisan;

it('download the latest browser version', function () {
    artisan('install:browser --latest')
        ->expectsOutputToContain('Downloading Google Chrome Browser')
        ->expectsOutputToContain('downloaded')
        ->assertSuccessful();

    expect(File::exists(join_paths(env('HOME'), '.google-for-testing', 'chrome-mac-arm64.zip')))
        ->toBeTrue();
});

it('it download the browser version []', function () {

})->todo();

it('download the browser on other path', function () {

})->todo();

it('try to download a pre-existing browser version', function () {

})->todo();

it('try to download a non existing browser version', function () {

})->todo();
