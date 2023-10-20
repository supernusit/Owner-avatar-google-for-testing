<?php

namespace App\Commands;

use App\GoogleDownloadable;
use App\OperatingSystem;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\error;
use function Laravel\Prompts\spin;

class InstallBrowserCommand extends InstallCommand
{
    protected int $component = GoogleDownloadable::BROWSER;

    protected $name = 'install:browser';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Install Google Browser';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
}
