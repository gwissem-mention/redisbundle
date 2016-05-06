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
            ->prototype('scalar')->end()
            ->end()
        ->end();

        return $node;
    }
}
