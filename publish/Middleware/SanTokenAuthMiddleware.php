<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Lazarini\HyperfSantoken\AuthManager;
use App\Model\User;


class SanTokenAuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AuthManager $auth
    ) {}

    public function process(
        ServerRequestInterface $request, 
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if ($token = $this->auth->extractTokenFromHeader($request->getHeaderLine('Authorization'))) {
            if ($payload = $this->auth->check($token)) {
                $user = User::find($payload['user_id']);
                $request = $request->withAttribute('user', $user);
            }
        }
        
        return $handler->handle($request);
    }
}