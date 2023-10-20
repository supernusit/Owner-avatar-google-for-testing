<?php

namespace App\Commands;

use App\GoogleDownloadable;

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
