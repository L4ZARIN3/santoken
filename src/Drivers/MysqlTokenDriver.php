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

use Hyperf\Database\ConnectionResolverInterface;
use Lazarini\HyperfSantoken\Contracts\TokenDriverInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class MysqlTokenDriver implements TokenDriverInterface
{
    private ConnectionResolverInterface $db;

    private LoggerInterface $logger;

    public function __construct(ConnectionResolverInterface $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    public function create(string $token, array $data): void
    {
        try {
            $this->db->connection()->table('auth_tokens')->insert([
                'token' => $token,
                'user_id' => $data['user_id'],
                'abilities' => json_encode($data['abilities']),
                'created_at' => $data['created_at'],
            ]);
        } catch (Throwable $e) {
            $this->logger->error('Failed to create token: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function check(string $token): ?array
    {
        try {
            $result = $this->db->connection()->table('auth_tokens')
                ->where('token', $token)
                ->first();

            return $result ? (array) $result : null;
        } catch (Throwable $e) {
            $this->logger->error('Failed to check token: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    public function update(string $token, array $data): void
    {
        $this->db->connection()->table('auth_tokens')
            ->where('token', $token)
            ->update(['abilities' => json_encode($data['abilities'])]);
    }

    public function revokeToken(string $token): void
    {
        try {
            $this->db->connection()->table('auth_tokens')
                ->where('token', $token)
                ->delete();
        } catch (Throwable $e) {
            $this->logger->error('Failed to delete token: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function getUserTokens(int $userId): array
    {
        try {
            $tokens = $this->db->connection()->table('auth_tokens')
                ->where('user_id', $userId)
                ->pluck('token')
                ->toArray();

            return $tokens ?: [];
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
