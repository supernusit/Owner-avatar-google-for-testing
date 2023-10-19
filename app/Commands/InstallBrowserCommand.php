<?php

namespace App\Commands;

use App\Facades\GoogleForTesting;
use App\GoogleDownloadable;
use App\OperatingSystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use function Laravel\Prompts\error;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\search;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;
use function Laravel\Prompts\info;
use function Termwind\render;

class InstallBrowserCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'install:browser
                            {--ver=115.0.5763.0 : Install specific version}
                            {--latest : Install the latest version}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Install Google Browser from';


    protected array $platforms = [
        'linux' => 'linux64',
        'mac-arm' => 'mac-arm64',
        'mac-intel' => 'mac-x64',
        'win' => 'win64',
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        if (empty($downloadable = $this->version())) {
            error("There' no versions available for [{$this->option('ver')}]");

            return self::FAILURE;
        }

        $os = OperatingSystem::id();

        $version = $downloadable->getVersion();

        spin(
            callback: fn () => download($downloadable->getChromeBrowserURL($this->platforms[$os]), $this->filename($os)),
            message: "Downloading Google Chrome Browser [$version]"
        );

        outro("Google Chrome Browser [$version] downloaded");

        return self::SUCCESS;
    }

    protected function version(): GoogleDownloadable|null
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
            label: "We found similar versions, please choose one",
            options: fn () => $versions->mapWithKeys(fn ($d) => [$d->getVersion() => $d->getVersion()])->all(),
            placeholder: 'Choose your prefer version'
        );

        return GoogleForTesting::getVersion($version);
    }

    protected function filename(string $os): string
    {
        $folder = join_paths(getenv('HOME'), '.google-for-testing');

        File::ensureDirectoryExists($folder);

        return $folder.DIRECTORY_SEPARATOR.'chrome-'.$this->platforms[$os].'.zip';
    }

    public function message(string $text, string $type = 'line'): void
    {
        $color = match ($type) {
            'success' => 'bg-green',
            'warning' => 'bg-yellow',
            'error'   => 'bg-red',
            'info'    => 'bg-blue',
            default   => 'bg-gray-600',
        };

        $type = str($type)->upper();

        render(<<<HTML
        <p>
            <span class="text-white $color px-2 mr-2">$type</span>

            <span>$text</span>
        </p>
        HTML);
    }
}
