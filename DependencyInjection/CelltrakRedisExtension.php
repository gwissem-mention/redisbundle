<?php
namespace Celltrak\RedisBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;


class CelltrakRedisExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor      = new Processor();
        $configSchema   = new CelltrakRedisConfiguration();
        $config         = $processor
                            ->processConfiguration($configSchema, $configs);

        $clients = $config['clients'];
        $this->initializeClients($clients, $container);
    }

    protected function initializeClients(
        array $clients,
        ContainerBuilder $container
    ) {
        $redisClass = 'Redis';

        foreach ($clients as $clientName => $clientParams) {
            $serviceId = "celltrak_redis.{$clientName}";
            $alias = "{$clientName}_celltrak_redis";

            $def = new Definition($redisClass);
            $container->setDefinition($serviceId, $def);
            $container->setAlias($alias, $serviceId);

            $args = [
                $clientParams['host'],
                $clientParams['port'],
                $clientParams['timeout']
            ];
            $def->addMethodCall('connect', $args);

            if ($clientParams['auth']) {
                $args = [
                    $clientParams['auth']
                ];
                $def->addMethodCall('auth', $args);
            }

            if ($clientParams['database'] != 1) {
                $args = [
                    $clientParams['database']
                ];
                $def->addMethodCall('select', $args);
            }

            if ($clientParams['key_prefix']) {
                $args = [
                    \Redis::OPT_PREFIX,
                    $clientParams['key_prefix']
                ];
                $def->addMethodCall('setOption', $args);
            }

            if ($clientParams['scan_retry']) {
                $args = [
                    \Redis::OPT_SCAN,
                    \Redis::SCAN_RETRY
                ];
                $def->addMethodCall('setOption', $args);
            }
        }
    }

}
