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
use SkNd\MediaBundle\Entity\MediaResource;
use SkNd\MediaBundle\Entity\MediaResourceCache;


class LoadMediaResources implements FixtureInterface, \Symfony\Component\DependencyInjection\ContainerAwareInterface {
    
    private $container; 
    private $em;
    
    //in order to get access to methods controlled by the container, it can be automatically injected using this method
    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container = null){
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getEntityManager();
        
    }
    
    public function load(ObjectManager $manager){
        //delete all records from the media resources and cache table first 
        foreach($this->em->getRepository('SkNdMediaBundle:MediaResource')->findAll() as $mr){
            $manager->remove($mr);
        }
        $manager->flush();

        //add some media resources
        
        //basic media resource with just media type
        $mr = new MediaResource();
        $mr->setAPI($this->getAPI());
        $mr->setMediaType($this->getMediaType('film'));
        
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
