<?php

namespace App;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleForTesting
{
    protected static string $started = '115.0.5763.0';

    protected static string $latest = 'https://googlechromelabs.github.io/chrome-for-testing/last-known-good-versions.json';

    protected static string $versions = 'https://googlechromelabs.github.io/chrome-for-testing/known-good-versions.json';

    protected static string $downloads = 'https://googlechromelabs.github.io/chrome-for-testing/known-good-versions-with-downloads.json';

    public function getLatestVersion(): ?GoogleDownloadable
    {
        $response = Http::get(static::$latest);

        $channel = $response->json('channels')['Stable'];

        $version = $channel['version'];

        return static::getVersion($version);
    }

    public function getVersion(string $version): ?GoogleDownloadable
    {
        $response = Http::get(static::$downloads);

        $exact = collect($response->json('versions'))
            ->first(fn (array $item) => $item['version'] == $version);

        if (empty($exact)) {
            return null;
        }

        return GoogleDownloadable::makeFromArray($exact);
    }

    public function getMilestone(string $milestone): ?Collection
    {
        $response = Http::get(static::$downloads);

        $versions = collect($response->json('versions'))
            ->filter(fn (array $item) => Str::before($item['version'], '.') == $milestone)
            ->map(fn (array $version) => GoogleDownloadable::makeFromArray($version));

        if ($versions->isEmpty()) {
            return null;
        }

        return $versions;
    }
}
