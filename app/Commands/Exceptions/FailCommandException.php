<?php

namespace App\Commands\Exceptions;

use Illuminate\Contracts\Process\ProcessResult;

class FailCommandException extends \Exception
{
    protected string $command;

    public function __construct(protected ProcessResult $result)
    {
        $this->command = $result->command;

        parent::__construct($result->errorOutput(), $result->exitCode());
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getError(): string
    {
        return $this->getMessage();
    }
}
