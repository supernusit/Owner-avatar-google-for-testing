<?php

namespace App\Commands;

use App\GoogleDownloadable;
use App\OperatingSystem;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\error;
use function Laravel\Prompts\spin;

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

        try {
            spin(
                callback: fn () => $downloadable->download(GoogleDownloadable::BROWSER, $this->getDownloadDirectory(), $this->platforms[$os], true),
                message: "Downloading Google Chrome Browser [$version]"
            );

            $this->message("Google Chrome Browser unzip it on [{$this->getDownloadDirectory()}]", 'info');
        } catch (\Throwable $e) {
            Log::error($e->getMessage());

            error("Unable to download/install Google Chrome Browser [$version]");

            return self::FAILURE;
        }

        $this->message("Google Chrome Browser [$version] downloaded", 'success');

        return self::SUCCESS;
    }

    protected function getDownloadDirectory(): string
    {
        if (! $this->option('path')) {
            return $this->getBasePath();
        }

        return $this->option('path');
    }
}
