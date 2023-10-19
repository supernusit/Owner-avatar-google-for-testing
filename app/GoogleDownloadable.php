<?php

namespace App;

use Illuminate\Support\Str;

class GoogleDownloadable
{
    protected function __construct(
        protected string $version,
        protected string $revision,
        protected array $driverDownloads,
        protected array $browserDownloads
    ) {
        //
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getMilestone(): string
    {
        return Str::of($this->version)->before('.');
    }

    /**
     * @throws \RuntimeException if the required platform doesn't exist
     */
    public function getChromeBrowserURL(string $platform): string
    {
        $item = collect($this->browserDownloads)->first(fn (array $item) => $item['platform'] === $platform);

        if (empty($item)) {
            throw new \RuntimeException("The URL for the platform [$platform] you requested, it's not available");
        }

        return $item['url'];
    }

    public static function make(string $version, string $revision, array $driverDownloads, array $browserDownloads): static
    {
        return new static($version, $revision, $driverDownloads, $browserDownloads);
    }

    public static function makeFromArray(array $data): static
    {
        $downloads = $data['downloads'];

        $version = $data['version'];
        $revision = $data['revision'];
        $driverDownloads = $downloads['chromedriver'];
        $browserDownloads = $downloads['chrome'];

        return static::make($version, $revision, $driverDownloads, $browserDownloads);
    }
}
