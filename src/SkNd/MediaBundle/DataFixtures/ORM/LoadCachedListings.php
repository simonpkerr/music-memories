<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * LoadCachedListings loads fixtures to test the cached listings
 * @author Simon Kerr
 * @version 1.0
 * to execute = php app/console doctrine:fixtures:load --fixtures=/path/to/fixture1 --fixtures=/path/to/fixture2 --append
 */
namespace SkNd\MediaBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SkNd\MediaBundle\Entity\MediaResourceListingsCache;


class LoadCachedListings implements FixtureInterface, ContainerAwareInterface {
    
    private $container; 
    private $em;
    
    //in order to get access to methods controlled by the container, it can be automatically injected using this method
    public function setContainer(ContainerInterface $container = null){
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getEntityManager();
    }
    
    public function load(ObjectManager $manager){
        //delete all records from the cached listings table first 
        $q = $this->em->createQuery('DELETE from SkNd\MediaBundle\Entity\MediaResourceListingsCache');
        $q->execute();
        
        //valid film listing
        $cachedListing = new MediaResourceListingsCache();
        $cachedListing->setAPI($this->getAPI());
        $cachedListing->setMediaType($this->getMediaType("film"));
        $cachedListing->setDateCreated(new \DateTime("now"));
        $cachedListing->setXmlData($this->getXmlData());
        $manager->persist($cachedListing);
        
        //valid film listing with specific decade and genre
        $cachedListing = new MediaResourceListingsCache();
        $cachedListing->setAPI($this->getAPI());
        $cachedListing->setMediaType($this->getMediaType("film"));
        $cachedListing->setDecade($this->getDecade("1980s"));
        $cachedListing->setGenre($this->getGenre("science-fiction", "film"));
        $cachedListing->setDateCreated(new \DateTime("now"));
        $cachedListing->setXmlData($this->getXmlData());
        $manager->persist($cachedListing);
        
        //valid film listing with specific decade, genre and keywords
        $cachedListing = new MediaResourceListingsCache();
        $cachedListing->setAPI($this->getAPI());
        $cachedListing->setMediaType($this->getMediaType("film"));
        $cachedListing->setDecade($this->getDecade("1980s"));
        $cachedListing->setGenre($this->getGenre("science-fiction", "film"));
        $cachedListing->setKeywords("aliens");
        $cachedListing->setDateCreated(new \DateTime("now"));
        $cachedListing->setXmlData($this->getXmlData());
        $manager->persist($cachedListing);
        
        //valid film listing with specific decade, genre, keywords and page
        $cachedListing = new MediaResourceListingsCache();
        $cachedListing->setAPI($this->getAPI());
        $cachedListing->setMediaType($this->getMediaType("film"));
        $cachedListing->setDecade($this->getDecade("1980s"));
        $cachedListing->setGenre($this->getGenre("science-fiction", "film"));
        $cachedListing->setKeywords("aliens");
        $cachedListing->setPage(2);
        $cachedListing->setDateCreated(new \DateTime("now"));
        $cachedListing->setXmlData($this->getXmlData());
        $manager->persist($cachedListing);
        
        //old tv listing 
        $cachedListing = new MediaResourceListingsCache();
        $cachedListing->setAPI($this->getAPI());
        $cachedListing->setMediaType($this->getMediaType("tv"));
        $cachedListing->setDateCreated(new \DateTime('2000-01-01'));
        $cachedListing->setXmlData($this->getXmlData());
        $manager->persist($cachedListing); 
        
        $manager->flush(); 
    }
    
    private function getAPI(){
        //create a new API, or use a reference with the ordered fixture interface. 
        //http://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html
        return $this->em->getRepository('SkNdMediaBundle:API')->find('1');
    }
    
    private function getMediaType($mediaType){
        return $this->em->getRepository('SkNdMediaBundle:MediaType')->getMediaTypeBySlug($mediaType);
    }
    
    private function getDecade($decade){
        return $this->em->getRepository('SkNdMediaBundle:Decade')->getDecadeBySlug($decade);
    }
    
    private function getGenre($genre, $media){
        return $this->em->getRepository('SkNdMediaBundle:Genre')->getGenreBySlugAndMedia($genre, $media);
    }
    
    
    private function getXmlData(){
        return '<?xml version="1.0" ?><items><item id="1"></item></items>';
    }
    
    
    
}

?>
