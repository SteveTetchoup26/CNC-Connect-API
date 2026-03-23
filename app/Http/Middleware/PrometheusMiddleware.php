<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use Symfony\Component\HttpFoundation\Response;

class PrometheusMiddleware
{
    public function __construct(private CollectorRegistry $registry) {}

   public function handle(Request $request, Closure $next): Response
{
    $start    = microtime(true);
    $response = $next($request);

    if ($request->is('metrics')) {
        return $response;
    }

    // ← Entourer dans try/catch pour éviter l'échec en test/CI
    try {
        $duration = microtime(true) - $start;
        $route    = $request->route()?->getName() ?? $request->getPathInfo();
        $method   = $request->method();
        $status   = (string) $response->getStatusCode();

        try {
            $counter = $this->registry->registerCounter(
                'laravel', 'http_requests_total',
                'Total HTTP requests', ['method', 'route', 'status']
            );
        } catch (\Prometheus\Exception\MetricsRegistrationException $e) {
            $counter = $this->registry->getCounter('laravel', 'http_requests_total');
        }
        $counter->inc([$method, $route, $status]);

        try {
            $histogram = $this->registry->registerHistogram(
                'laravel', 'http_request_duration_seconds',
                'HTTP request duration in seconds',
                ['method', 'route'],
                [0.01, 0.05, 0.1, 0.25, 0.5, 1.0, 2.5, 5.0]
            );
        } catch (\Prometheus\Exception\MetricsRegistrationException $e) {
            $histogram = $this->registry->getHistogram('laravel', 'http_request_duration_seconds');
        }
        $histogram->observe($duration, [$method, $route]);

        try {
            $gauge = $this->registry->registerGauge(
                'laravel', 'memory_usage_bytes', 'Memory used by PHP process'
            );
        } catch (\Prometheus\Exception\MetricsRegistrationException $e) {
            $gauge = $this->registry->getGauge('laravel', 'memory_usage_bytes');
        }
        $gauge->set(memory_get_peak_usage(true));

    } catch (\Throwable $e) {
        // Redis indisponible (CI, tests) — on ignore silencieusement
        \Illuminate\Support\Facades\Log::warning('Prometheus metrics unavailable: ' . $e->getMessage());
    }

    return $response;
}
}
