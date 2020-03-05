<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace YouMi\Aliyun\ApiGateway;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ApiGatewayInterface::class => ApiGateway::class,
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
                    'description' => 'The config for aliyun api.',
                    'source' => __DIR__ . '/../publish/aliyun_api.php',
                    'destination' => BASE_PATH . '/config/autoload/aliyun_api.php',
                ],
            ],
        ];
    }
}
