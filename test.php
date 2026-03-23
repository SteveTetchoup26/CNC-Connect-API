<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
define('LARAVEL_START', microtime(true));
require '/var/www/vendor/autoload.php';

$app = require_once '/var/www/bootstrap/app.php';
echo "Bootstrap OK\n";

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();

// Désactiver le handler d'exceptions Laravel
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    function() {
        return new class extends Illuminate\Foundation\Exceptions\Handler {
            public function __construct() {}
            public function render($request, Throwable $e) {
                echo "REAL ERROR: " . $e->getMessage() . "\n";
                echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
                echo $e->getTraceAsString();
                exit(1);
            }
            public function report(Throwable $e) {
                echo "REPORT: " . $e->getMessage() . "\n";
            }
        };
    }
);

$response = $kernel->handle($request);
echo "Response: " . $response->getStatusCode() . "\n";
echo $response->getContent();
