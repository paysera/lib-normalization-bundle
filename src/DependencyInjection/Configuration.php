<?php
declare(strict_types=1);

namespace Paysera\Bundle\NormalizationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('paysera_normalization');
        $rootNode = method_exists($treeBuilder, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('paysera_normalization')
        ;

        $children = $rootNode->children();

        $this->registerNormalizers($children->arrayNode('register_normalizers'));

        return $treeBuilder;
    }

    private function registerNormalizers(ArrayNodeDefinition $node)
    {
        $builder = $node->children();
        $builder
            ->arrayNode('date_time')
            ->children()
            ->scalarNode('format')
            ->isRequired()
        ;
    }
}
