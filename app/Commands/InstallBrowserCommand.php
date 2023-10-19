<?php

namespace App\Commands;

use App\Facades\GoogleForTesting;
use App\GoogleDownloadable;
use App\OperatingSystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

        $filename = $this->getBasePath('chrome-'.$this->platforms[$os].'.zip');

        try {
            $result = true;

            spin(
                callback: fn () => download($downloadable->getChromeBrowserURL($this->platforms[$os]), $filename),
                message: "Downloading Google Chrome Browser [$version]"
            );

            spin(
                callback: fn () => unzip($filename),
                message: 'Unzipping Google Chrome Browser',
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            $result = false;
        } finally {
            File::delete($filename);
        }

        if (! $result) {
            error("Unable to download/install Google Chrome Browser [$version]");

            return self::FAILURE;
        }

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

    protected function getBasePath(?string $path = null): string
    {
        $folder = join_paths(getenv('HOME'), '.google-for-testing');

        File::ensureDirectoryExists($folder);

        return join_paths($folder, $path);
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
