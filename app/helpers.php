<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\HttpException;

if (! function_exists('join_paths')) {
    /**
     * Join two or more paths together
     */
    function join_paths(string ...$paths): string
    {
        $first = array_shift($paths);

        $paths = array_filter(
            array_map(fn (string $p) => trim($p, DIRECTORY_SEPARATOR), $paths)
        );

        return implode(DIRECTORY_SEPARATOR, [rtrim($first, DIRECTORY_SEPARATOR), ...$paths]);
    }
}

if (! function_exists('download')) {

    /**
     * Download the zip file from Google Labs
     *
     * @throws Exception if the file already exists
     * @throws Http
     */
    function download(string $url, string $file, bool $force = false): void
    {
        if (File::exists($file) && ! $force) {
            throw new Exception('The file ['.basename($file).'] already exists');
        }

        File::delete($file);

        $response = Http::withOptions(['stream' => true])->get($url)->toPsrResponse();

        $body = $response->getBody();

        if ($response->getStatusCode() !== 200) {
            throw new HttpException($response->getStatusCode());
        }

        try {
            while (! $body->eof()) {
                File::append($file, $body->read(2048));
            }
        } catch (\RuntimeException) {
            // Continue
        }
    }
}

if (! function_exists('unzip')) {
    /**
     * Unzip the given filename
     *
     * This function will attempt to unzip the given zip file name into the given location, but if the location
     * is not provided, we'll use the file directory.
     *
     * @param  string  $filename The file name of the ZIP file
     * @param  ?string  $to The location where to extract the content
     */
    function unzip(string $filename, string $to = null): void
    {
        if (! extension_loaded('zip')) {
            throw new \RuntimeException('Extension [ext-zip] not found');
        }

        $zip = new \ZipArchive();
        $to ??= dirname($filename);

        try {
            $zip->open($filename);

            $zip->extractTo($to);
        } finally {
            $zip->close();
        }
    }
}
