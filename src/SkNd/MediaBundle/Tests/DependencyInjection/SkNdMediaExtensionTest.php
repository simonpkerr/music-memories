<?php
/*
 * Original code Copyright (c) 2011 Simon Kerr
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use SkNd\MediaBundle\DependencyInjection\SkNdMediaExtension;
use Symfony\Component\Yaml\Parser;

class SkNdMediaExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected $configuration;
    
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testMediaAPILoadThrowsExceptionUnlessAPIsSet()
    {
        $loader = new SkNdMediaExtension();
        $config = $this->getConfig();
        unset($config['mediaapi']['apis']);
        $loader->load(array($config), new ContainerBuilder());
    }
    
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testMediaAPILoadThrowsExceptionUnlessAmazonAPISet()
    {
        $loader = new SkNdMediaExtension();
        $config = $this->getConfig();
        unset($config['mediaapi']['apis']['amazonapi']);
        $loader->load(array($config), new ContainerBuilder());
    }
    
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testMediaAPILoadThrowsExceptionUnlessYouTubeAPISet()
    {
        $loader = new SkNdMediaExtension();
        $config = $this->getConfig();
        unset($config['mediaapi']['apis']['youtubeapi']);
        $loader->load(array($config), new ContainerBuilder());
    }
    
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testMediaAPILoadThrowsExceptionUnlessAmazonAccessParamsSet()
    {
        $loader = new SkNdMediaExtension();
        $config = $this->getConfig();
        unset($config['mediaapi']['apis']['amazonapi']['access_params']);
        $loader->load(array($config), new ContainerBuilder());
    }
    
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testMediaAPILoadThrowsExceptionUnlessAmazonPublicKeySet()
    {
        $loader = new SkNdMediaExtension();
        $config = $this->getConfig();
        unset($config['mediaapi']['apis']['amazonapi']['access_params']['amazon_public_key']);
        $loader->load(array($config), new ContainerBuilder());
    }
    
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testMediaAPILoadThrowsExceptionUnlessAmazonPrivateKeySet()
    {
        $loader = new SkNdMediaExtension();
        $config = $this->getConfig();
        unset($config['mediaapi']['apis']['amazonapi']['access_params']['amazon_private_key']);
        $loader->load(array($config), new ContainerBuilder());
    }
    
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testMediaAPILoadThrowsExceptionUnlessAmazonAssociateTagSet()
    {
        $loader = new SkNdMediaExtension();
        $config = $this->getConfig();
        unset($config['mediaapi']['apis']['amazonapi']['access_params']['amazon_associate_tag']);
        $loader->load(array($config), new ContainerBuilder());
    }
    
    /**
     * getEmptyConfig
     *
     * @return array
     */
    protected function getConfig()
    {
        $yaml = <<<EOF
mediaapi:
  debug_mode: false
  apis: 
    amazonapi:
      access_params:
        amazon_public_key: 12345
        amazon_private_key: 12345
        amazon_associate_tag: 12345
    youtubeapi: ~
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }
    
   
}
?>
