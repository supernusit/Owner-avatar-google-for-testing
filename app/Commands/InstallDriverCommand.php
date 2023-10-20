<?php

namespace App\Commands;

use App\GoogleDownloadable;

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
