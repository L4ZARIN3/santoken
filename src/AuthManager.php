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

namespace Lazarini\HyperfSantoken;

use Hyperf\Carbon\Carbon;
use Lazarini\HyperfSantoken\Contracts\TokenDriverInterface;
use Psr\Log\LoggerInterface;

class AuthManager
{
    public function __construct(
        private TokenDriverInterface $tokenDriver,
        private LoggerInterface $logger
    ) {
    }

    public function createToken(int $userId, array $abilities = []): string
    {
        $tokenSecret = bin2hex(random_bytes(32));
        $hashedSecret = $this->hashToken($tokenSecret);

        $this->tokenDriver->create($hashedSecret, [
            'user_id' => $userId,
            'abilities' => $abilities,
            'created_at' => Carbon::now(),
        ]);

        return $tokenSecret;
    }

    public function check(string $token): ?array
    {
        return $this->tokenDriver->check($this->hashToken($token));
    }

    public function can(string $token, string $ability): bool
    {
        return ($data = $this->check($token)) && in_array($ability, $data['abilities'], true);
    }

    public function addAbility(string $token, string $ability): void
    {
        $this->updateAbilities($token, fn (array $abilities) => array_merge($abilities, [$ability]));
    }

    public function removeAbility(string $token, string $ability): void
    {
        $this->updateAbilities($token, fn (array $abilities) => array_values(array_diff($abilities, [$ability])));
    }

    public function destroyCurrentToken(string $token): void
    {
        $this->tokenDriver->destroyUserCurrentToken($this->hashToken($token));
    }

    public function extractTokenFromHeader(?string $authorizationHeader): ?string
    {
        return ($authorizationHeader && str_starts_with($authorizationHeader, 'Bearer '))
            ? substr($authorizationHeader, 7)
            : null;
    }

    private function updateAbilities(string $token, callable $modifier): void
    {
        $hashedSecret = $this->hashToken($token);
        if ($data = $this->check($token)) {
            $data['abilities'] = $modifier($data['abilities']);
            $this->tokenDriver->update($hashedSecret, $data);
        }
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }
}
