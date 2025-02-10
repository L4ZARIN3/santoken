<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    'driver' => env('SANTOKEN_DRIVER', 'mysql'),
    'redis_prefix' => env('SANTOKEN_REDIS_PREFIX', 'auth_'),
];