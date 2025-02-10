<?php

declare(strict_types=1);

use Hyperf\Context\ApplicationContext;
use Lazarini\HyperfSantoken\AuthManager;
use Hyperf\HttpServer\Contract\RequestInterface;

if (!function_exists('auth')) {
    function auth(): AuthManager
    {
        return ApplicationContext::getContainer()->get(AuthManager::class);
    }
}

if (!function_exists('current_user')) {
    function current_user(): ?object
    {
        $request = ApplicationContext::getContainer()->get(RequestInterface::class);
        return $request->getAttribute('user');
    }
}

if (!function_exists('auth_id')) {
    function auth_id(): ?int
    {
        return current_user()?->id;
    }
}

if (!function_exists('auth_check')) {
    function auth_check(): bool
    {
        return current_user() !== null;
    }
}