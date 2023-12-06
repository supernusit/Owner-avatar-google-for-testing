<?php

namespace App\Commands\Exceptions;

class DoesNotSupportWindowsException extends \Exception
{
    public function __construct()
    {
        parent::__construct('This command is not supported on Windows.');
    }
}
