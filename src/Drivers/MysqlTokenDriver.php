<?php

declare(strict_types=1);

namespace BrunoLazarini\HyperfAuth;

use Hyperf\Database\ConnectionResolverInterface;

class MysqlTokenDriver implements TokenDriverInterface
{
    private ConnectionResolverInterface $db;

    public function __construct(ConnectionResolverInterface $db)
    {
        $this->db = $db;
    }

    public function create(string $token, array $data): void
    {
        $this->db->connection()->table('auth_tokens')->insert([
            'token'      => $token,
            'user_id'    => $data['user_id'],
            'abilities'  => json_encode($data['abilities']),
            'created_at' => $data['created_at'],
        ]);
    }

    public function check(string $token): ?array
    {
        return $this->db->connection()->table('auth_tokens')
            ->where('token', $token)
            ->first();
    }

    public function update(string $token, array $data): void
    {
        $this->db->connection()->table('auth_tokens')
            ->where('token', $token)
            ->update(['abilities' => json_encode($data['abilities'])]);
    }

    public function destroyUserCurrentToken(string $token): void {}
    public function destroyAllUserTokens(string $token): void {}
}
