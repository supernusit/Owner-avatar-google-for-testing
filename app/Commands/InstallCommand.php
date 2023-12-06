<?php

namespace App\Commands;

use App\Facades\GoogleForTesting;
use App\GoogleDownloadable;
use App\OperatingSystem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

use function Laravel\Prompts\error;
use function Laravel\Prompts\search;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;
use function Termwind\render;

abstract class InstallCommand extends Command
{
    protected int $component;

    protected array $platforms = [
        'linux' => 'linux64',
        'mac-arm' => 'mac-arm64',
        'mac-intel' => 'mac-x64',
        'win' => 'win64',
    ];

    protected function configure(): void
    {
        $this->addOption(
            'ver',
            null,
            InputOption::VALUE_OPTIONAL,
            'Install a specific version',
            '115.0.5763.0',
        );

        $this->addOption(
            'latest',
            null,
            InputOption::VALUE_NONE,
            'Install the latest version',
        );

        $this->addOption(
            'path',
            null,
            InputOption::VALUE_OPTIONAL,
            'Specify the path where to download it',
        );
    }

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
                callback: fn () => $downloadable->download($this->component, $this->getDownloadDirectory(), $this->platforms[$os], true),
                message: "Downloading Google Chrome {$this->getComponentName()} [$version]"
            );

            $this->message("Google Chrome {$this->getComponentName()} unzip it on [{$this->getDownloadDirectory()}]", 'info');
        } catch (\Throwable $e) {
            Log::error($e->getMessage());

            error("Unable to download/install Google Chrome {$this->getComponentName()} [$version]");

            return self::FAILURE;
        }

        $this->message("Google Chrome {$this->getComponentName()} [$version] downloaded", 'success');

        return self::SUCCESS;
    }

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

    protected function getBasePath(?string $path = null): string
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
        return $this->option('path') ?? $this->getBasePath();
    }

    protected function getComponent(): int
    {
        return $this->component;
    }

    protected function getComponentName(): string
    {
        return $this->getComponent() === GoogleDownloadable::BROWSER ? 'Browser' : 'Driver';
    }
}
