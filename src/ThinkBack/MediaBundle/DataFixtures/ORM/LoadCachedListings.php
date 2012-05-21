<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * LoadCachedListings loads fixtures to test the cached listings
 * @author Simon Kerr
 * @version 1.0
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
        $cachedListing->setMediaType($this->getMediaType());
        $cachedListing->setDateCreated(new \DateTime("now"));
        
        $manager->persist($cachedListing);
        $manager->flush(); 
    }
    
    private function getAPI(){
        //create a new API, or use a reference with the ordered fixture interface. 
        //http://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html
        return $this->em->getRepository('ThinkBackMediaBundle:API');
    }
    
    private function getMediaType(){
        
    }
    
}

?>
