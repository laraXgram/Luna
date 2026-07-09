<?php

namespace LaraGram\Luna\Commands;

use LaraGram\Console\Command;
use LaraGram\Luna\Ssr\HttpGateway;
use LaraGram\Console\Attribute\AsCommand;

#[AsCommand(name: 'luna:stop-ssr')]
class StopSsr extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'luna:stop-ssr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stop the Luna SSR server';

    /**
     * Stop the Luna SSR server.
     */
    public function handle(HttpGateway $gateway): int
    {
        $url = $gateway->getProductionUrl('/shutdown');

        $ch = curl_init($url);
        curl_exec($ch);

        if (curl_error($ch) !== 'Empty reply from server') {
            $this->error('Unable to connect to Luna SSR server.');

            return self::FAILURE;
        }

        $this->info('Luna SSR server stopped.');

        curl_close($ch);

        return self::SUCCESS;
    }
}
