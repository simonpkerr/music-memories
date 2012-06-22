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
    /*private $cachedXMLResponse;
    private $liveXMLResponse;
    private $cachedYouTubeXMLResponse;
    private $liveYouTubeXMLResponse;
    private $testAmazonAPI;
    private $testYouTubeAPI;
    private $session;*/
    
    
    public function setup(){
        $this->client = static::createClient();
        $this->client->followRedirects(true);
        $kernel = static::createKernel();
        $kernel->boot();
        $this->router = $kernel->getContainer()->get('router');
        
    }


    public function testAddMediaResourceToMemoryWallWhenNotLoggedInRedirectsToLogin(){
        $url = $this->router->generate('memoryWallAddMediaResource', array(
            'slug'  => 'my-memory-wall-1',
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
            '_username' => 'testuser2',
            '_password' => 'testuser2',
            
        ); 
        
        $crawler = $this->client->submit($form, $params);
        $url = $this->router->generate('memoryWallAddMediaResource', array(
            'slug'  => 'my-memory-wall-1',
            'api'   => 'amazonapi',
            'id'    => 'B00061S0QE',
        ));
        $crawler = $this->client->request('GET', $url);
        
        
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
    
}

