<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis;

class MetricsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CollectorRegistry::class, function () {
            Redis::setDefaultOptions([
                'host'            => env('REDIS_HOST', 'redis'),
                'port'            => (int) env('REDIS_PORT', 6379),
                'password'        => env('REDIS_PASSWORD', null),
                'timeout'         => 0.1,
                'read_timeout'    => 10,
                'persistent_connections' => false,
            ]);

            return new CollectorRegistry(new Redis());
        });
    }
}
