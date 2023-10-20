<?php

namespace App\Commands;

use App\Facades\GoogleForTesting;
use App\GoogleDownloadable;
use App\OperatingSystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use function Laravel\Prompts\error;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\search;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;
use function Laravel\Prompts\info;
use function Termwind\render;

class InstallBrowserCommand extends InstallCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'install:browser
                            {--ver=115.0.5763.0 : Install specific version}
                            {--latest : Install the latest version}
                            {--path= : Specify the path where to download the browser}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Install Google Browser';


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

        $filename = $this->getFilename($os);

        try {
            spin(
                callback: fn () => $downloadable->download(GoogleDownloadable::BROWSER, $this->platforms[$os], $filename),
                message: "Downloading Google Chrome Browser [$version]"
            );

            $dir = dirname($filename);

            $this->message("Google Chrome Browser unzip it on [$dir]", 'info');
        } catch (\Throwable $e) {
            Log::error($e->getMessage());

            error("Unable to download/install Google Chrome Browser [$version]");

            return self::FAILURE;
        } finally {
            File::delete($filename);
        }

        $this->message("Google Chrome Browser [$version] downloaded", 'success');

        return self::SUCCESS;
    }

    protected function getFilename(string $os): string
    {
        $filename = 'chrome-'.$this->platforms[$os].'.zip';

        if (! $this->option('path')) {
            return $this->getBasePath($filename);
        }

        return join_paths($this->option('path'), $filename);
    }
}
