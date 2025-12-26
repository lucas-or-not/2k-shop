<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        // Set APP_KEY for testing if not already set
        if (empty($_ENV['APP_KEY']) && empty(getenv('APP_KEY'))) {
            // Generate a proper 32-byte key for AES-256
            $key = base64_encode(random_bytes(32));
            putenv('APP_KEY=base64:' . $key);
            $_ENV['APP_KEY'] = 'base64:' . $key;
        }

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
