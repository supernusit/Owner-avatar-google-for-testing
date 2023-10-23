<?php

namespace App\Commands;

use App\OperatingSystem;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
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
                            {--p|port=* : The port from where to start a new server}
                            {--path= : The absolute path of where to find Google Chrome Driver binary}';

    protected $description = 'Manage Google Chrome Driver';

    protected string $port = '9515';

    protected array $platforms = [
        'linux' => 'linux64',
        'mac-arm' => 'mac-arm64',
        'mac-intel' => 'mac-x64',
        'win' => 'win64',
    ];

    protected array $commands = [
        'start' => './chromedriver --log-level=ALL --port={port} &',
        'pid' => "ps aux | grep '[c]hromedriver --log-level=ALL {options}' | awk '{print $2,$13}'",
        'stop' => 'kill -9 {pid}',
    ];

    public function handle(): int
    {
        $action = $this->argument('action') ?? select('Select an action to perform', [
            'start' => 'Start a new server',
            'stop' => 'Stop a server',
            'restart' => 'Restart a server',
            'status' => 'Status of a server',
            'list' => 'List all the server available in the system',
            'kill' => 'Kill all the servers available in the system',
        ]);

        $callable = match ($action) {
            'start' => $this->start(...),
            'stop' => $this->stop(...),
            'restart' => $this->restart(...),
            'status' => $this->status(...),
            'list' => $this->list(...),
            'kill' => $this->kill(...),
        };

        if ($action === 'kill' || $action === 'list') {
            return $callable();
        }

        return $this->getPorts()->map(fn (string $port) => $callable(port: $port))
            // Reduce the result of every callable to a single SUCCESS or FAILURE value
            ->reduce(fn (int $results, int $result) => $results && $result, self::FAILURE);
    }

    protected function start(string $port): int
    {
        if ($pid = $this->getProcessID($port)) {
            warning("[PID: $pid]: There's a server running already on port [$port]");

            return self::FAILURE;
        }

        intro("Stating Google Chrome Driver on port [$port]");

        $this->command('start', ['{port}' => $port]);

        info('Google Chrome Driver server is up and running');

        return self::SUCCESS;
    }

    public function stop(string $port): int
    {
        intro("Stopping Google Chrome Driver on port [$port]");

        $pid = $this->getProcessID($port);

        if (empty($pid)) {
            warning("There's no server to stop on port [$port]");

            return self::FAILURE;
        }

        $this->command('stop', ['{pid}' => $pid]);

        info("Google Chrome Driver server stopped on port [$port]");

        return self::SUCCESS;
    }

    protected function restart(string $port): int
    {
        intro("Restarting Google Chrome Driver on port [$port]");

        $pid = $this->getProcessID($port);

        if (empty($pid)) {
            warning("There's no server to restart on port [$port]");

            return self::FAILURE;
        }

        $this->command('stop', ['{pid}' => $pid]);

        $this->command('start', ['{port}' => $port]);

        info("Google Chrome Driver server restarted on port [$port]");

        return self::SUCCESS;
    }

    protected function status(string $port): int
    {
        intro("Getting Google Chrome Driver status on port [$port]");

        $pid = $this->getProcessID($port);

        if (empty($pid)) {
            warning("There's no server available on port [$port]");

            return self::FAILURE;
        }

        $response = Http::get('http://localhost:9515/status');

        $data = $response->json('value');

        if (array_key_exists('error', $data) || ! $data['ready']) {
            error('There was a problem, we cannot establish connection with the server');

            return self::FAILURE;
        }

        info('Google Chrome server status: [OK]');

        return self::SUCCESS;
    }

    protected function list(): int
    {
        info('Listing all the servers available');

        $result = $this->getProcessIDs();

        if (empty($result)) {
            warning("There' no servers available to list");

            return self::FAILURE;
        }

        $this->table(['PID', 'PORT'], $result);

        return self::SUCCESS;
    }

    protected function kill(): int
    {
        $pids = $this->getProcessIDs();

        if (empty($pids)) {
            warning("There' no servers to kill");

            return self::FAILURE;
        }

        if (! $this->confirm('Are you sure you want to do this?')) {
            return self::SUCCESS;
        }

        info('Stopping all the Google Chrome Driver servers that are available in the system');

        $pids
            ->each(function (array $data) {
                info("Stopping Google Chrome Driver [PID: {$data['pid']}]");

                $this->command('stop', ['{pid}' => $data['pid']]);
            });

        return self::SUCCESS;
    }

    protected function command(string $cmd, array $with)
    {
        return Process::command(
            Str::replace(
                collect($with)->keys(),
                collect($with)->values(),
                $this->commands[$cmd]
            )
        )->path($this->getChromeDriverDirectory())->run();
    }

    protected function getProcessID(string $port): ?int
    {
        $process = $this->command('pid', ['{options}' => '--port='.$port]);

        $output = explode(' ', trim($process->output()));

        return (int) $output[0] ?: null;
    }

    protected function getProcessIDs(): ?Collection
    {
        $process = $this->command('pid', ['{options}' => '']);

        if (empty($process->output())) {
            return null;
        }

        $raw = explode("\n", trim($process->output()));

        return collect($raw)->map(function (string $data) {
            $data = explode(' ', $data);

            return ['pid' => $data[0], 'port' => Str::remove('--port=', $data[1])];
        });
    }

    protected function getPorts(): Collection
    {
        return collect($this->option('port') ? [...$this->option('port')] : $this->port)->unique()->filter();
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
