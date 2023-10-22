<?php

namespace App\Commands;

use App\OperatingSystem;
use Illuminate\Console\Command;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;

use Illuminate\Support\Str;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\select;
use function Laravel\Prompts\warning;

class DriverManagerCommand extends Command
{
    protected $signature = 'manage:driver
                            {action? : The action you want to perform on Google\'s Chrome Driver}
                            {--path= : The absolute path of where to find Google Chrome Driver binary}';

    protected $description = 'Manage Google Chrome Driver';

    protected array $platforms = [
        'linux' => 'linux64',
        'mac-arm' => 'mac-arm64',
        'mac-intel' => 'mac-x64',
        'win' => 'win64',
    ];

    protected array $commands = [
        'start' => "./chromedriver --log-level=ALL --port=9515 &",
        'pid' => "ps aux | grep '[c]hromedriver --log-level=ALL --port=9515' | awk '{print $2}'",
        'stop' => 'kill -9 {pid}'
    ];

    public function handle(): int
    {
        $action = $this->argument('action') ?? select('Select an action to perform', [
            'start' => 'Start a new server',
            'stop' => 'Stop a server',
            'restart' => 'Restart a server',
            'status' => 'Status of a server',
        ]);

        $result = match ($action) {
            'start' => $this->start(),
            'stop' => $this->stop(),
            'restart' => $this->restart(),
            'status' => $this->status(),
        };

        return $result;
    }

    protected function start(): int
    {
        if ($pid = $this->getProcessID()) {
            warning("[PID: $pid]: There's a server running already on port [9515]");

            return self::FAILURE;
        }

        intro('Stating Google Chrome Driver on port [9515]');

        $this->command($this->commands['start'])->run();

        info('Google Chrome Driver server is up and running');

        return self::SUCCESS;
    }

    public function stop(): int
    {
        intro('Stopping Google Chrome Driver on port [9515]');

        $pid = $this->getProcessID();

        if (empty($pid)) {
            warning("There's no server to stop on port [9515]");

            return self::FAILURE;
        }

        $this->command(Str::replace('{pid}', $pid, $this->commands['stop']))->run();

        info('Google Chrome Driver server stopped on port [9515]');

        return self::SUCCESS;
    }

    protected function restart(): int
    {
        intro('Restarting Google Chrome Driver on port [9515]');

        $pid = $this->getProcessID();

        if (empty($pid)) {
            info("There's no server to restart on port [9515]");

            return self::FAILURE;
        }

        $this->command(Str::replace('{pid}', $pid, $this->commands['stop']))->run();

        $this->command($this->commands['start'])->run();

        info('Google Chrome Driver server restarted on port [9515]');

        return self::SUCCESS;
    }

    protected function status(): int
    {
        intro('Getting Google Chrome Driver status on port [9515]');

        $pid = $this->getProcessID();

        if (empty($pid)) {
            info("There's no server available on port [9515]");

            return self::FAILURE;
        }

        $response = Http::get('http://localhost:9515/status');

        $data = $response->json('value');

        if (array_key_exists('error', $data) || ! $data['ready']) {
            error('There was a problem, we cannot estAblish connection with the server');

            return self::FAILURE;
        }

        info('Google Chrome server status: [OK]');

        return self::SUCCESS;
    }

    protected function command(string $cmd): PendingProcess
    {
        return Process::command($cmd)->path($this->getChromeDriverDirectory());
    }

    protected function getProcessID(): int|null
    {
        $process = $this->command($this->commands['pid'])->run();

        return (int) trim($process->output()) ?: null;
    }

    protected function getChromeDriverDirectory(): string
    {
        return $this->option('path')
            ?? join_paths(
                getenv('HOME'),
                '.google-for-testing',
                'chromedriver-'.$this->platforms[OperatingSystem::id()],
            );
    }
}
