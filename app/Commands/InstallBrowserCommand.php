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
                            {--latest : Install the latest version}';

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

        $filename = $this->getBasePath('chrome-'.$this->platforms[$os].'.zip');

        try {
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

            error("Unable to download/install Google Chrome Browser [$version]");

            return self::FAILURE;
        } finally {
            File::delete($filename);
        }

        outro("Google Chrome Browser [$version] downloaded");

        return self::SUCCESS;
    }
}
