<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Contract\RequestInterface;
use Lazarini\HyperfSantoken\AuthManager;

if (! function_exists('auth')) {
    function auth(): AuthManager
    {
        return ApplicationContext::getContainer()->get(AuthManager::class);
    }
}

if (! function_exists('current_user')) {
    function current_user(): ?object
    {
        $request = ApplicationContext::getContainer()->get(RequestInterface::class);
        return $request->getAttribute('user');
    }
}

if (! function_exists('auth_id')) {
    function auth_id(): ?int
    {
        return current_user()?->id;
    }
}

if (! function_exists('auth_check')) {
    function auth_check(): bool
    {
        return current_user() !== null;
    }
}
