<?php

namespace ThinkBack\MediaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;
/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('thinkback_media');
        
        $rootNode
        ->children()
                ->arrayNode('amazonapi')
                    ->children()
                        ->arrayNode('access_params')
                            ->children()
                                ->scalarNode('amazon_public_key')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('amazon_private_key')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('amazon_associate_tag')->isRequired()->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                        ->arrayNode('amazon_signed_request')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->defaultValue('ThinkBack\MediaBundle\MediaAPI\AmazonSignedRequest')->end()
                            ->end()
                        ->end()
                   ->end()
                ->end()
                ->arrayNode('youtubeapi')
                    ->children()
                        ->arrayNode('youtube_request_object')
                            ->defaultNull()
                            ->children()
                                ->scalarNode('class')->defaultNull()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
        ->end()
        ;
                 
        return $treeBuilder;
    }
}
