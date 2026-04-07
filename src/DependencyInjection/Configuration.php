<?php

declare(strict_types=1);

namespace Bash\S365IDMappingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('s365_id_mapping');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->scalarNode('base_url')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('username')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('project')->isRequired()->cannotBeEmpty()->end()
            ->end();

        return $treeBuilder;
    }
}
