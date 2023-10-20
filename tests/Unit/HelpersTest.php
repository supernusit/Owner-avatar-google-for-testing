<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

dataset('paths', fn () => [
    'simple path' => [
        'paths' => ['/this', 'is', 'a', 'path/'],
        'result' => '/this/is/a/path',
    ],
    'weird path' => [
        'paths' => ['/just/another/', '/path/to/', '/join//'],
        'result' => '/just/another/path/to/join',
    ],
    'strange path' => [
        'paths' => ['what', '//is//this/', '//path//'],
        'result' => 'what/is//this/path',
    ],
]);

afterAll(fn () => File::delete(join_paths(__DIR__, '..', 'files', 'file.txt')));

it('join paths', function ($paths, $result) {
    expect(join_paths(...$paths))
        ->toBe($result);
})->with('paths');

it('download a file', function () {
    Http::fake();

    $fileMock = File::partialMock();

    $fileMock
        ->shouldReceive('exists')
        ->andReturn(false, true);

    $fileMock
        ->shouldReceive('delete')
        ->andReturn(false);

    $fileMock
        ->expects('append')
        ->andReturn();

    expect(fn () => download('https://fake-download.com', '/path/to/a/file.zip'))
        ->not->toThrow(\Exception::class)
        ->and(File::exists('/path/to/a/file.zip'))
        ->toBeTrue();
});

it('try to download a file that already exists', function () {
    Http::fake();

    $fileMock = File::partialMock();

    $fileMock
        ->shouldReceive('exists')
        ->andReturnTrue();

    expect(fn () => download('https://fake-download.com', '/path/to/a/file.zip'))
        ->toThrow(\Exception::class);
});

it('delete a pre-existing file to download it again', function () {
    Http::fake();

    $fileMock = File::partialMock();

    $fileMock
        ->shouldReceive('exists')
        ->andReturnTrue();

    $fileMock
        ->shouldReceive('delete')
        ->andReturn(true);

    $fileMock
        ->expects('append')
        ->andReturn();

    expect(fn () => download('https://fake-download.com', '/path/to/a/file.zip', true))
        ->not->toThrow(\Exception::class)
        ->and(File::exists('/path/to/a/file.zip'))
        ->toBeTrue();
});

test('unzip zip file', function () {
    $zip = join_paths(__DIR__, '..', 'files', 'file.zip');
    $file = join_paths(dirname($zip), 'file.txt');

    expect(fn () => unzip($zip))
        ->not->toThrow(\Exception::class)
        ->not->toThrow(RuntimeException::class)
        ->and(File::exists($file))
        ->toBeTrue();
});
