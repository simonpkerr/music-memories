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
use \PHPUnit_Framework_TestCase;

class SkNdMediaExtensionTest extends PHPUnit_Framework_TestCase
{
    protected $configuration;
    protected $loader;
    
    public function tearDown(){
        unset($this->configuration);
        unset($this->loader);
    }
    
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testMediaAPILoadThrowsExceptionUnlessAPIsSet()
    {
        $this->loader = new SkNdMediaExtension();
        $this->configuration = $this->getConfig();
        unset($this->configuration['mediaapi']['apis']);
        $this->loader->load(array($this->configuration), new ContainerBuilder());
    }
    
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testMediaAPILoadThrowsExceptionUnlessAmazonAPISet()
    {
        $this->loader = new SkNdMediaExtension();
        $this->configuration = $this->getConfig();
        unset($this->configuration['mediaapi']['apis']['amazonapi']);
        $this->loader->load(array($this->configuration), new ContainerBuilder());
    }
    
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testMediaAPILoadThrowsExceptionUnlessYouTubeAPISet()
    {
        $this->loader = new SkNdMediaExtension();
        $this->configuration = $this->getConfig();
        unset($this->configuration['mediaapi']['apis']['youtubeapi']);
        $this->loader->load(array($this->configuration), new ContainerBuilder());
    }
    
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testMediaAPILoadThrowsExceptionUnlessAmazonAccessParamsSet()
    {
        $this->loader = new SkNdMediaExtension();
        $this->configuration = $this->getConfig();
        unset($this->configuration['mediaapi']['apis']['amazonapi']['access_params']);
        $this->loader->load(array($this->configuration), new ContainerBuilder());
    }
    
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testMediaAPILoadThrowsExceptionUnlessAmazonPublicKeySet()
    {
        $this->loader = new SkNdMediaExtension();
        $this->configuration = $this->getConfig();
        unset($this->configuration['mediaapi']['apis']['amazonapi']['access_params']['amazon_public_key']);
        $this->loader->load(array($this->configuration), new ContainerBuilder());
    }
    
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testMediaAPILoadThrowsExceptionUnlessAmazonPrivateKeySet()
    {
        $this->loader = new SkNdMediaExtension();
        $this->configuration = $this->getConfig();
        unset($this->configuration['mediaapi']['apis']['amazonapi']['access_params']['amazon_private_key']);
        $this->loader->load(array($this->configuration), new ContainerBuilder());
    }
    
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testMediaAPILoadThrowsExceptionUnlessAmazonAssociateTagSet()
    {
        $this->loader = new SkNdMediaExtension();
        $this->configuration = $this->getConfig();
        unset($this->configuration['mediaapi']['apis']['amazonapi']['access_params']['amazon_associate_tag']);
        $this->loader->load(array($this->configuration), new ContainerBuilder());
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
