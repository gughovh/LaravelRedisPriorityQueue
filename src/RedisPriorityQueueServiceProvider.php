<?php

namespace App\Providers;

use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;

class RedisPriorityQueueServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        /** @var QueueManager $manager */
        $manager = $this->app['queue'];

        $manager->addConnector('redis-priority', function() {
            return new RedisPriorityConnector($this->app['redis']);
        });
    }

}
