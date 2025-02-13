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
use Psr\Log\LoggerInterface;
use Throwable;

class RedisTokenDriver implements TokenDriverInterface
{
    private Redis $redis;

    private string $prefix;

    private LoggerInterface $logger;

    public function __construct(Redis $redis, string $prefix, LoggerInterface $logger)
    {
        $this->redis = $redis;
        $this->prefix = $prefix;
        $this->logger = $logger;
    }

    public function create(string $token, array $data): void
    {
        try {
            $this->redis->set($this->prefix . "tokens:{$token}", json_encode($data));
            $this->redis->sAdd($this->prefix . "user_tokens:{$data['user_id']}", $token);
        } catch (Throwable $e) {
            $this->logger->error('Failed to create token: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function check(string $token): ?array
    {
        try {
            $data = $this->redis->get($this->prefix . "tokens:{$token}");
            return $data ? json_decode($data, true) : null;
        } catch (Throwable $e) {
            $this->logger->error('Failed to check token: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    public function update(string $token, array $data): void
    {
        $this->redis->set($this->prefix . "tokens:{$token}", json_encode($data));
    }

    public function revokeToken(string $token): void
    {
        try {
            $data = $this->check($token);
            if ($data) {
                $this->redis->del($this->prefix . "tokens:{$token}");
                $this->redis->sRem($this->prefix . "user_tokens:{$data['user_id']}", $token);
            }
        } catch (Throwable $e) {
            $this->logger->error('Failed to delete token: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function getUserTokens(int $userId): array
    {
        try {
            return $this->redis->sMembers($this->prefix . "user_tokens:{$userId}") ?: [];
        } catch (Throwable $e) {
            $this->logger->error('Failed to get user tokens: ' . $e->getMessage(), [
                'user_id' => $userId,
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    public function destroyAllUserTokens($userId): void
    {
    }
}
