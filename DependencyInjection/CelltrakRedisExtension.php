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

        var_dump($config);
    }

}
