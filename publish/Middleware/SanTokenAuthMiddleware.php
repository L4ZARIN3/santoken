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

use App\Model\User;
use Lazarini\HyperfSantoken\AuthManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SanTokenAuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AuthManager $auth
    ) {
    }

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
