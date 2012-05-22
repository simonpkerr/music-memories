<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * LoadCachedListings loads fixtures to test the cached listings
 * @author Simon Kerr
 * @version 1.0
 * to execute = php app/console doctrine:fixtures:load --fixtures=/path/to/fixture1 --fixtures=/path/to/fixture2 --append
 */
namespace ThinkBack\MediaBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use ThinkBack\MediaBundle\Entity\MediaResourceListingsCache;

class LoadCachedListings implements FixtureInterface, \Symfony\Component\DependencyInjection\ContainerAwareInterface {
    
    private $container; 
    private $em;
    
    //in order to get access to methods controlled by the container, it can be automatically injected using this method
    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container = null){
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getEntityManager();
    }
    
    public function load(ObjectManager $manager){
        //load a cached listing with valid api, mediatype and timestamp
        $cachedListing = new MediaResourceListingsCache();
        $cachedListing->setAPI($this->getAPI());
        $cachedListing->setMediaType($this->getMediaType('film'));
        $cachedListing->setDateCreated(new \DateTime("now"));
        $cachedListing->setXmlData($this->getXmlData());
        
        $manager->persist($cachedListing);
        $manager->flush(); 
        
        
    }
    
    private function getAPI(){
        //create a new API, or use a reference with the ordered fixture interface. 
        //http://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html
        return $this->em->getRepository('ThinkBackMediaBundle:API')->find('1');
    }
    
    private function getMediaType($mediaType){
        return $this->em->getRepository('ThinkBackMediaBundle:MediaType')->find('1');
    }
    
    private function getXmlData(){
        return '<?xml version="1.0" ?><items><item id="1"></item></items>';
    }
    
    
    
}

?>
