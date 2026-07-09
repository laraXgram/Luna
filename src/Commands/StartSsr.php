<?php

namespace LaraGram\Luna\Commands;

use LaraGram\Console\Command;
use LaraGram\Luna\Ssr\BundleDetector;
use LaraGram\Luna\Ssr\SsrException;
use LaraGram\Console\Attribute\AsCommand;
use LaraGram\Console\Process\ExecutableFinder;
use LaraGram\Console\Process\Process;

#[AsCommand(name: 'luna:start-ssr')]
class StartSsr extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'luna:start-ssr {--runtime= : The runtime to use (e.g. `node`, `bun`, or an absolute path)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the Luna SSR server';

    /**
     * Start the Luna SSR server.
     */
    public function handle(): int
    {
        if (! config('luna.ssr.enabled', true)) {
            $this->error('Luna SSR is not enabled. Enable it via the `luna.ssr.enabled` config option.');

            return self::FAILURE;
        }

        $bundle = (new BundleDetector)->detect();
        $configuredBundle = config('luna.ssr.bundle');

        if ($bundle === null) {
            $this->error(
                $configuredBundle
                    ? 'Luna SSR bundle not found at the configured path: "'.$configuredBundle.'"'
                    : 'Luna SSR bundle not found. Set the correct Luna SSR bundle path in your `luna.ssr.bundle` config.'
            );

            return self::FAILURE;
        } elseif ($configuredBundle && $bundle !== $configuredBundle) {
            $this->warn('Luna SSR bundle not found at the configured path: "'.$configuredBundle.'"');
            $this->warn('Using a default bundle instead: "'.$bundle.'"');
        }

        $runtime = $this->option('runtime') ?? config('luna.ssr.runtime', 'node');

        if (config('luna.ssr.ensure_runtime_exists', false) && ! (new ExecutableFinder)->find($runtime)) {
            $this->error('SSR runtime "'.$runtime.'" could not be found.');

            return self::FAILURE;
        }

        $this->callSilently('luna:stop-ssr');

        $process = app(Process::class, ['command' => [$runtime, $bundle]]);
        $process->setTimeout(null);
        $process->start();

        if (extension_loaded('pcntl')) {
            $stop = function () use ($process) {
                $process->stop();
            };
            pcntl_async_signals(true);
            pcntl_signal(SIGINT, $stop);
            pcntl_signal(SIGQUIT, $stop);
            pcntl_signal(SIGTERM, $stop);
        }

        foreach ($process as $type => $data) {
            if ($process::OUT === $type) {
                $this->info(trim($data));
            } else {
                $this->error(trim($data));
                report(new SsrException($data));
            }
        }

        return self::SUCCESS;
    }
}
