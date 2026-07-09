<?php

namespace LaraGram\Luna\Commands;

use LaraGram\Console\Command;
use LaraGram\Luna\Ssr\Gateway;
use LaraGram\Luna\Ssr\HasHealthCheck;
use LaraGram\Console\Attribute\AsCommand;

#[AsCommand(name: 'luna:check-ssr')]
class CheckSsr extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'luna:check-ssr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the Luna SSR server health status';

    /**
     * Check the Luna SSR server health status.
     */
    public function handle(Gateway $gateway): int
    {
        if (! $gateway instanceof HasHealthCheck) {
            $this->error('The SSR gateway does not support health checks.');

            return self::FAILURE;
        }

        ($check = $gateway->isHealthy())
            ? $this->info('Luna SSR server is running.')
            : $this->error('Luna SSR server is not running.');

        return $check ? self::SUCCESS : self::FAILURE;
    }
}
