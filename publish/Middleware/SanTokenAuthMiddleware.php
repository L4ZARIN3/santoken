<?php

declare(strict_types=1);

namespace App\Middleware;

use Lazarini\HyperfSantoken\AuthManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;

class SanTokenAuthMiddleware implements MiddlewareInterface
{

    #[Inject]
    protected AuthManager $authManager;

    public function __construct(protected ContainerInterface $container, protected HttpResponse $response) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getHeaderLine('Authorization');
        $token = $this->authManager->extractTokenFromHeader($token);

        if (!$token) {
            return $this->response->json([
                    'message' => 'Token not found',
                    'status' => 401
                ]);
        }

        $getToken = $this->authManager->check($token);

        if (!$getToken) {
            return $this->response->json([
                'message' => 'Token not found',
                'status' => 401
            ]);
        }

        $this->authManager->setUser($getToken);

        return $handler->handle($request);
    }
}
