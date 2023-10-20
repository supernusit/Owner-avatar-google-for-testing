<?php

namespace App\Commands;

use App\GoogleDownloadable;
use App\OperatingSystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\error;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\spin;

class InstallDriverCommand extends InstallCommand
{
    protected int $component = GoogleDownloadable::DRIVER;

    protected $name = 'install:driver';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Install Google Driver';
}
