<?php

namespace App\Commands;

use App\OperatingSystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\error;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\spin;

class InstallDriverCommand extends InstallCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'install:driver
                            {--ver=115.0.5763.0 : Install specific version}
                            {--latest : Install the latest version}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Install Google Driver';

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

        $filename = $this->getBasePath('chromedriver-'.$this->platforms[$os].'.zip');

        try {
            $result = true;

            spin(
                callback: fn () => download($downloadable->getChromeDriverURL($this->platforms[$os]), $filename),
                message: "Downloading Google Chrome Driver [$version]"
            );

            spin(
                callback: fn () => unzip($filename),
                message: 'Unzipping Google Chrome Driver',
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            error("Unable to download/install Google Chrome Driver [$version]");

            return self::FAILURE;
        } finally {
            File::delete($filename);
        }

        outro("Google Chrome Driver [$version] downloaded");

        return self::SUCCESS;
    }
}
