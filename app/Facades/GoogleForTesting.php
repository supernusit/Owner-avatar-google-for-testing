<?php

namespace App\Facades;

use App\GoogleDownloadable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static null|GoogleDownloadable getLatestVersion() Get the latest version of Google Chrome Browser and Google Chrome Driver
 * @method static null|GoogleDownloadable getVersion(string $version) Get a specific version
 * @method static null|Collection<GoogleDownloadable> getMilestone(string $version) Get a collection with all the versions available for a Milestone
 */
class GoogleForTesting extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return 'gft';
    }
}
