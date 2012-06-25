<?php

namespace SkNd\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use SkNd\MediaBundle\MediaAPI\MediaAPI;
use SkNd\MediaBundle\Entity\MediaSelection;
use SkNd\MediaBundle\Entity\MediaResource;

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MWCMediaResourcesTest for all operations of memory walls related to mediaresources
 * the DataFixtures/ORM/LoadUsers fixtures file should be loaded first
 * @author Simon Kerr
 * @version 1.0
 * debug cmd: set XDEBUG_CONFIG=idekey=netbeans-xdebug 
 */


class MWCMediaResourcesTest extends WebTestCase
{
    private $client;
    private $router;
    private $em;
    private $mediaapi;
    private $session;
    private $mediaSelection;
    /*private $cachedXMLResponse;
    private $liveXMLResponse;
    private $cachedYouTubeXMLResponse;
    private $liveYouTubeXMLResponse;
    private $testAmazonAPI;
    private $testYouTubeAPI;
    */
    
    
    public function setup(){
        $this->client = static::createClient();
        $this->client->followRedirects(true);
        $kernel = static::createKernel();
        $kernel->boot();
        $this->router = $kernel->getContainer()->get('router');
 
        //$this->em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        
        //$this->session = $kernel->getContainer()->get('session');
         
       $this->mediaapi = $kernel->getContainer()->get('sk_nd_media.mediaapi');
        $this->session = $this->mediaapi->getSession();
        $this->em = $this->mediaapi->getEntityManager();
        //$this->mediaapi->setSession($this->session);
        //$this->mediaapi->setEntityManager($this->em);
        
        /*$mediaType = $this->em->getRepository('SkNdMediaBundle:MediaType')->getMediaTypeBySlug('film');
        $this->mediaSelection = new MediaSelection();
        $this->mediaSelection->setMediaTypes($mediaType);
        
        $this->session->set('mediaSelection', $this->mediaSelection);*/
    }
    
    private function getNewMediaResource($id = 'testMR'){
        $mr = new MediaResource();
        $mr->setId($id);
        $mr->setAPI($this->em->getRepository('SkNdMediaBundle:API')->find(1));
        $mr->setMediaType($this->em->getRepository('SkNdMediaBundle:MediaType')->find(1));
        return $mr;
    }
    
    public function testAddMediaResourceToMemoryWallWhenNotLoggedInRedirectsToLogin(){
        $url = $this->router->generate('memoryWallAddMediaResource', array(
            'slug'  => 'my-memory-wall',
            'api'   => 'amazonapi',
            'id'    => '12345',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->selectButton('Login')->count() > 0);
    }
    
    public function testAddMediaResourceAddsResourceIfOnlyOneWallExists(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
        );
       
        $crawler = $this->client->submit($form, $params);
        
        //***** TRY MANUALLY SETTING THE MEDIA SELECTION PARAMETERS FIRST OR LOOKING AT A DETAILS PAGE FIRST
        $this->mediaapi->getMediaSelection(array(
            'media' => 'film',
        ));
        
        $url = $this->router->generate('memoryWallAddMediaResource', array(
            'slug'  => 'my-memory-wall',
            'api'   => 'amazonapi',
            'id'    => 'testMR',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($this->em->getRepository('SkNdMediaBundle:MediaResource')->findOneBy(array('id' => 'testMR'))->count() > 0);
        
    }
  
    public function testAddMediaResourceToMemoryWallWhenNotLoggedInRedirectsToLoginThenToSelectWallViewIfMoreThanOneWallExists(){
        
    }
    
    public function testAddMediaResourceToNonExistentWallThrowsException(){
        
    }
    
    public function testAddMediaResourceToOthersWallThrowsException(){
        
    }
    
    public function testAddInvalidMediaResourceToValidMemoryWallThrowsException(){
        
    }

    public function testAddIdenticalMediaResourceTwiceToMemoryWallThrowsException(){
        
    }

    public function testAddMediaResourceToMemoryWallShowsMemoryWallWithResource(){
        
    }
    
    public function testRemoveMediaResourceFromMemoryWallOnlyRemovesMediaResource(){
        
    }
    
    public function testRemoveOtherUsersMediaResourceFromMemoryWallThrowsException(){
        
    }
    
    public function testRemoveMediaResourceWhenNotLoggedInRedirectsToLogin(){
        
    }
    
    public function testMultipleUsersCanAddTheSameMediaResourceToTheirWalls(){
        
    }
    
    public function testIdenticalMediaResourcesCanBeAddedToDifferentUserWalls(){
        
    }
    
    public function testDeleteMemoryWallAlsoRemovesAssociatedMediaResources(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser2',
            '_password' => 'testuser2',
            
        ); 
        $crawler = $this->client->submit($form, $params);
        
        //add a resource to memory wall 1
        //$mr = getNewMediaResource('newMR');        
        //$this->mediaapi->addMediaResource();
        
        $url = $this->router->generate('memoryWallAddMediaResource', array(
            'slug'  => 'my-memory-wall-1',
            'api'   => 'amazonapi',
            'id'    => 'newMR'
        ));
        $crawler = $this->client->request('GET', $url);
        
        $url = $this->router->generate('memoryWallDelete', array('slug' => 'my-memory-wall-1'));
        $crawler = $this->client->request('GET', $url);

        $url = $this->router->generate('memoryWallDeleteConfirm', array('slug' => 'my-memory-wall-1'));
        $crawler = $this->client->request('GET', $url);
        
        $this->assertTrue($this->em->getRepository('SkNdMediaBundle:MediaResource')->find('newMR')->count() == 0);
    }
    
    
    
}

?>