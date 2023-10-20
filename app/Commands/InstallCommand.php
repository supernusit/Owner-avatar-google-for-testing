<?php

namespace App\Commands;

use App\Facades\GoogleForTesting;
use App\GoogleDownloadable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use function Laravel\Prompts\search;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;
use function Termwind\render;

abstract class InstallCommand extends Command
{
    protected function version(): ?GoogleDownloadable
    {
        if ($this->option('latest')) {
            return GoogleForTesting::getLatestVersion();
        }

        $version = $this->option('ver');

        $downloadable = spin(
            callback: fn () => GoogleForTesting::getVersion($version),
            message: "Searching for version [$version]"
        );

        if (filled($downloadable)) {
            return $downloadable;
        }

        $versions = GoogleForTesting::getMilestone(Str::before($version, '.'));

        if (empty($versions)) {
            return null;
        }

        warning("There isn't an exact version [$version]");

        $version = search(
            label: 'We found similar versions, please choose one',
            options: fn () => $versions->mapWithKeys(fn ($d) => [$d->getVersion() => $d->getVersion()])->all(),
            placeholder: 'Choose your prefer version'
        );

        return GoogleForTesting::getVersion($version);
    }

    protected function getBasePath(string $path = null): string
    {
        $folder = join_paths(getenv('HOME'), '.google-for-testing');

        File::ensureDirectoryExists($folder);

        return join_paths($folder, $path ?? '');
    }

    public function message(string $text, string $type = 'line'): void
    {
        $color = match ($type) {
            'success' => 'bg-green',
            'warning' => 'bg-yellow',
            'error' => 'bg-red',
            'info' => 'bg-blue',
            default => 'bg-gray-600',
        };

        $type = str($type)->upper();

        render(<<<HTML
        <p>
            <span class="text-white $color px-2 mr-2">$type</span>

            <span>$text</span>
        </p>
        HTML);
    }

    protected function getDownloadDirectory(): string
    {
        if (! $this->option('path')) {
            return $this->getBasePath();
        }

        return $this->option('path');
    }
}
