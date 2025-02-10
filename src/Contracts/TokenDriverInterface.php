<?php

declare(strict_types=1);

namespace Lazarini\HyperfSantoken\Contracts;

interface TokenDriverInterface
{
    public function create(string $token, array $data): void;
    public function check(string $token): ?array;
    public function update(string $token, array $data): void;
    public function destroyUserCurrentToken(string $token): void;
    public function destroyAllUserTokens(string $token): void;
}
