<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * Configuration ensures that the configuration passed for the given environment is valid 
 * and supplies defaults where appropriate
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('SkNd_media');
        $rootNode
        ->children()
            ->arrayNode('mediaapi')->isRequired()
                ->children()
                    ->booleanNode('debug_mode')->defaultValue(false)->end()
                    ->scalarNode('cache_path')->defaultValue('bundles/SkNd/cache/')->end()
                    ->arrayNode('apis')->isRequired()
                        ->children()
                            ->arrayNode('amazonapi')
                            ->isRequired()
                                ->children()
                                    ->arrayNode('access_params')->isRequired()
                                        ->children()
                                            ->scalarNode('amazon_public_key')->isRequired()->cannotBeEmpty()->end()
                                            ->scalarNode('amazon_private_key')->isRequired()->cannotBeEmpty()->end()
                                            ->scalarNode('amazon_associate_tag')->isRequired()->cannotBeEmpty()->end()
                                        ->end()
                                    ->end()//end of access params
                                    ->arrayNode('amazon_signed_request')
                                    ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode('class')->defaultValue('SkNd\MediaBundle\MediaAPI\AmazonSignedRequest')->end()
                                        ->end()
                                    ->end()//end of amazon_signed_request
                                ->end()
                            ->end()//end of amazonapi
                            ->arrayNode('youtubeapi')
                            ->isRequired()
                                ->children()
                                    ->arrayNode('youtube_request_object')
                                    ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode('class')->defaultValue('Zend_Gdata_YouTube')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()//end of youtubeapi
                        ->end()
                    ->end()//end of apis
                ->end()

            ->end()//end of mediaapi                
        ->end()
        ;
        
        return $treeBuilder;
    }
}
