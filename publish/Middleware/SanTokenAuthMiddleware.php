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

namespace App\Middleware;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponseInterface;
use Lazarini\HyperfSantoken\AuthManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SanTokenAuthMiddleware implements MiddlewareInterface
{
    #[Inject]
    protected AuthManager $authManager;

    public function __construct(
        protected ContainerInterface $container,
        protected HttpResponseInterface $response
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getHeaderLine('Authorization');
        $token = $this->authManager->extractTokenFromHeader($token);

        if (empty($token) || ! $this->authManager->check($token)) {
            return $this->response->json([
                'error' => 'Invalid credentials',
            ])->withStatus(401);
        }

        $user = $this->authManager->check($token);
        $this->authManager->setUser($user);

        return $handler->handle($request);
    }
}
