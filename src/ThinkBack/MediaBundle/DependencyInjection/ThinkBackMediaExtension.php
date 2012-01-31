<?php

namespace ThinkBack\MediaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ThinkBackMediaExtension extends Extension
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
        
        //amazon params
        $container->setParameter('amazonapi.access_params', $config['amazonapi']['access_params']);
        $container->setParameter ('amazonapi.amazon_signed_request.class', $config['amazonapi']['amazon_signed_request']['class']);
        //$container->setParameter('amazonapi.params', $config['amazonapi']['params']);
        
        
        //youtube params
        if(isset($config['youtubeapi']['youtube_request_object'])){
            $yro = $config['youtubeapi']['youtube_request_object'];
            $container->getDefinition('think_back_media.youtubeapi')->replaceArgument(0, $yro);
        }
        
        /*if(isset($config['youtubeapi']['youtube_request_object'])){
            $container->setParameter('youtubeapi.youtube_request_object.class', $config['youtubeapi']['youtube_request_object']['class']);
            //$container->setParameter('youtubeapi.youtube_request_object', $config['youtubeapi']['youtube_request_object']);
        }*/
    }
}
