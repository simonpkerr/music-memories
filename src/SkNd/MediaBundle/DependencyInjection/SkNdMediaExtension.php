<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * @author Simon Kerr
 * @version 1.0
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 * SkNdMediaExtension Loads and manages configuration of the SkNdMedia bundle. 
 */

namespace SkNd\MediaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;



class SkNdMediaExtension extends Extension
{
    /**
     * {@inheritDoc}
     * any configs that come to the load method come as multi-dimensional arrays
     * so that the different environment config files can all be parsed. a method needs 
     * to be used to handle duplicate config parameters
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        
        //mediaapi params
        $container->setParameter('mediaapi.debug_mode', $config['mediaapi']['debug_mode']);
        $container->setParameter('mediaapi.apis', $config['mediaapi']['apis']);
        $container->setParameter('mediaapi.cache_path', $config['mediaapi']['cache_path']);

        $apis = $config['mediaapi']['apis'];
        //amazon params
        $container->setParameter('amazonapi.access_params', $apis['amazonapi']['access_params']);
        $container->setParameter ('amazonapi.amazon_signed_request.class', $apis['amazonapi']['amazon_signed_request']['class']);
        
        //youtube params
        $container->setParameter('youtubeapi.youtube_request_object.class', $apis['youtubeapi']['youtube_request_object']['class']);
        
    }
}
