<?php
namespace Celltrak\RedisBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;


/**
 * Enforces semantic configuration for CTLib bundle.
 *
 * @author Mike Turoff <mturoff@celltrak.com>
 */
class CelltrakRedisConfiguration implements ConfigurationInterface
{

    const DEFAULT_REDIS_PORT = 6379;



    public function getConfigTreeBuilder()
    {
        $tb = new TreeBuilder();
        $root = $tb->root('celltrak_redis');

        $root
            ->children()
                ->append($this->addClientsNode())
            ->end();

        return $tb;
    }

    protected function addClientsNode()
    {
        $tb = new TreeBuilder();
        $node = $tb->root('clients');

        $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('host')
                        ->isRequired()
                    ->end()
                    ->integerNode('port')
                        ->defaultValue(self::DEFAULT_REDIS_PORT)
                        ->min(1)
                    ->end()
                    ->scalarNode('auth')
                        ->defaultNull()
                    ->end()
                    ->integerNode('database')
                        ->defaultValue(0)
                        ->min(0)
                    ->end()
                    ->integerNode('timeout')
                        ->defaultValue(0)
                        ->min(0)
                    ->end()
                    ->scalarNode('key_prefix')
                        ->defaultNull()
                    ->end()
                    ->booleanNode('scan_retry')
                        ->defaultTrue()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $node;
    }
}
