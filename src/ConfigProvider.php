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

use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Redis\Redis;
use Lazarini\HyperfSantoken\Contracts\TokenDriverInterface;
use Lazarini\HyperfSantoken\Drivers\MysqlTokenDriver;
use Lazarini\HyperfSantoken\Drivers\RedisTokenDriver;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        if (file_exists(BASE_PATH . '/app/Helper/SanTokenHelper.php')) {
            require BASE_PATH . '/app/Helper/SanTokenHelper.php';
        }

        return [
            'dependencies' => [
                AuthManager::class => function (ContainerInterface $container) {
                    return new AuthManager(
                        $container->get(TokenDriverInterface::class),
                        $container->get(LoggerInterface::class)
                    );
                },
                TokenDriverInterface::class => function (ContainerInterface $container) {
                    /** @var ConfigInterface $config */
                    $config = $container->get(ConfigInterface::class);
                    $driver = $config->get('auth_santoken.driver', 'mysql');
                    $prefix = $config->get('auth_santoken.redis_prefix', 'auth_');

                    return match ($driver) {
                        'redis' => new RedisTokenDriver($container->get(Redis::class), $prefix),
                        default => new MysqlTokenDriver($container->get(ConnectionResolverInterface::class)),
                    };
                },
            ],
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The configuration file for Hyperf Auth.',
                    'source' => __DIR__ . '/../publish/Config/santoken.php',
                    'destination' => BASE_PATH . '/config/autoload/santoken.php',
                ],
                [
                    'id' => 'migrations',
                    'description' => 'The migration file for creating auth tokens table.',
                    'source' => __DIR__ . '/../publish/Migrations/2025_02_07_000000_create_auth_tokens_table.php',
                    'destination' => BASE_PATH . '/migrations/2025_02_07_000000_create_auth_tokens_table.php',
                ],
                [
                    'id' => 'helpers',
                    'description' => 'helper para autenticação.',
                    'source' => __DIR__ . '/../publish/Helper/SanTokenHelper.php',
                    'destination' => BASE_PATH . '/app/Helper/SanTokenHelper.php',
                ],
                [
                    'id' => 'middleware',
                    'description' => 'helper para autenticação.',
                    'source' => __DIR__ . '/../publish/Middleware/SanTokenAuthMiddleware.php',
                    'destination' => BASE_PATH . '/app/Middleware/SanTokenAuthMiddleware.php',
                ],
            ],
        ];
    }
}
