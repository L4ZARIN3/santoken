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

namespace Lazarini\HyperfSantoken\Drivers;

use Hyperf\Redis\Redis;
use Lazarini\HyperfSantoken\Contracts\TokenDriverInterface;

class RedisTokenDriver implements TokenDriverInterface
{
    private Redis $redis;

    private string $prefix;

    public function __construct(Redis $redis, string $prefix)
    {
        $this->redis = $redis;
        $this->prefix = $prefix;
    }

    public function create(string $token, array $data): void
    {
        $this->redis->set($this->prefix . "tokens:{$token}", json_encode($data));
        $this->redis->sAdd($this->prefix . "user_tokens:{$data['user_id']}", $token);
    }

    public function check(string $token): ?array
    {
        $data = $this->redis->get($this->prefix . "tokens:{$token}");
        return $data ? json_decode($data, true) : null;
    }

    public function update(string $token, array $data): void
    {
        $this->redis->set($this->prefix . "tokens:{$token}", json_encode($data));
    }

    public function destroyUserToken(string $token): void
    {
        $tokenData = $this->check($token);

        if ($tokenData) {
            $this->redis->sRem($this->prefix . "user_tokens:{$tokenData['user_id']}", $token);
        }

        $this->redis->del($this->prefix . "tokens:{$token}");
    }

    public function destroyAllUserTokens(string $token): void
    {
        // $tokenData = $this->check($token);

        // if ($tokenData) {
        //     $this->redis->del($this->prefix . "user_tokens:{$tokenData['user_id']}");
        // }

        // $this->redis->del($this->prefix . "tokens:{$token}");
    }
}
