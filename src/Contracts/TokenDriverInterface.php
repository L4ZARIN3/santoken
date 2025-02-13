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

namespace Lazarini\HyperfSantoken\Contracts;

interface TokenDriverInterface
{
    public function create(string $token, array $data): void;

    public function check(string $token): ?array;

    public function update(string $token, array $data): void;

    public function destroyUserToken(string $token): void;

    public function destroyAllUserTokens(string $token): void;
}
