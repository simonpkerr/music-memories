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

    public function __construct(){
        $this->client = static::createClient();
        $this->client->followRedirects(true);
        $kernel = static::createKernel();
        $kernel->boot();
        $this->router = $kernel->getContainer()->get('router');
        $this->mediaapi = $kernel->getContainer()->get('sk_nd_media.mediaapi');
        $this->session = $this->mediaapi->getSession();
        $this->em = $this->mediaapi->getEntityManager();
   
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

    public function testAddMediaResourceToInvalidAPIThrowsException(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser3',
            '_password' => 'testuser3',
        );
        $crawler = $this->client->submit($form, $params);
        
        $url = $this->router->generate('memoryWallAddMediaResource', array(
            'slug'  => 'my-memory-wall-2',
            'api'   => 'invalid-api',
            'id'    => 'testMR',
        ));
        $crawler = $this->client->request('GET', $url);
        //indicates runtime error
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
    }
    
    public function testAddMediaResourceAddsResourceIfOnlyOneWallExists(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser3',
            '_password' => 'testuser3',
        );
        $crawler = $this->client->submit($form, $params);
        
        $url = $this->router->generate('memoryWallAddMediaResource', array(
            'slug'  => 'my-memory-wall-2',
            'api'   => 'amazonapi',
            'id'    => 'testMR',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->filter('div.mediaResource')->count() > 0);
    }
  
    public function testAddMediaResourceToMemoryWallWhenNotLoggedInRedirectsToLoginThenToSelectWallViewIfMoreThanOneWallExists(){
                
        $url = $this->router->generate('memoryWallAddMediaResource', array(
            'api'   => 'amazonapi',
            'id'    => '111',
        ));
        $crawler = $this->client->request('GET', $url);
        
        //$crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
        );
        $crawler = $this->client->submit($form, $params);
        
        $this->assertTrue($crawler->filter('h1')->text() == 'Select a Memory Wall');
        $this->assertTrue($crawler->filter('ul.userMemoryWalls li')->count() > 1);
    }
    
    public function testAddMediaResourceToNonExistentWallThrowsException(){
        
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser3',
            '_password' => 'testuser3',
        );
        $crawler = $this->client->submit($form, $params);
        
        $url = $this->router->generate('memoryWallAddMediaResource', array(
            'slug'  => 'non-existent-wall',
            'api'   => 'amazonapi',
            'id'    => 'testMR',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }
    
    public function testAddMediaResourceToOthersWallThrowsException(){
        
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
        );
        $crawler = $this->client->submit($form, $params);
        
        $url = $this->router->generate('memoryWallAddMediaResource', array(
            'slug'  => 'my-memory-wall-1',
            'api'   => 'amazonapi',
            'id'    => 'testMR',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
    
    public function testAddIdenticalMediaResourceTwiceToMemoryWallThrowsException(){
 
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser3',
            '_password' => 'testuser3',
        );
        $crawler = $this->client->submit($form, $params);
        
        $url = $this->router->generate('memoryWallAddMediaResource', array(
            'slug'  => 'my-memory-wall-2',
            'api'   => 'amazonapi',
            'id'    => 'testMR',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->filter('body > div.flashMessages li.notice')->count() > 0);
 
    }
    
    public function testAddYouTubeThenAmazonItemCorrectlyAddsItemsToWall(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser3',
            '_password' => 'testuser3',
        );
        $crawler = $this->client->submit($form, $params);
        
        $url = $this->router->generate('memoryWallAddMediaResource', array(
            'slug'  => 'my-memory-wall-2',
            'api'   => 'youtubeapi',
            'id'    => 'testYTMR1',
        ));
        $crawler = $this->client->request('GET', $url);
        
        $url = $this->router->generate('memoryWallAddMediaResource', array(
            'slug'  => 'my-memory-wall-2',
            'api'   => 'amazonapi',
            'id'    => 'testAMR1',
        ));
        $crawler = $this->client->request('GET', $url);
        
        //$this->assertTrue($crawler->filter('body > h3:contains("Added from Amazon")')->siblings('div.mediaResource')->eq(1)->children('a:contains("mediadetails")')->count() > 0);
        //$this->assertTrue($crawler->filter('body > h3:contains("Added from YouTube")')->siblings('div.mediaResource')->eq(0)->children('a:contains("javascript")')->count() > 0);
        $this->assertTrue($crawler->filter('body > div:contains("Contents") h3')->eq(0)->count() > 0);
    }
    
    public function testMultipleUsersCanAddTheSameMediaResourceToTheirWalls(){

        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser3',
            '_password' => 'testuser3',
        );
        $crawler = $this->client->submit($form, $params);
        
        $url = $this->router->generate('memoryWallAddMediaResource', array(
            'slug'  => 'my-memory-wall-2',
            'api'   => 'amazonapi',
            'id'    => 'testuser-mr1',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->filter('div.mediaResource')->count() > 0);
        
    }
    
    public function testIdenticalMediaResourcesCanBeAddedToDifferentWallsOfSameUser(){
 
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
        );
        $crawler = $this->client->submit($form, $params);
        
        $url = $this->router->generate('memoryWallAddMediaResource', array(
            'slug'  => 'private-wall',
            'api'   => 'amazonapi',
            'id'    => 'testuser-mr1',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->filter('div.mediaResource')->count() > 0);
        
    }

    public function testRemoveMediaResourceFromMemoryWallShowsConfirmation(){
        
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser3',
            '_password' => 'testuser3',
        );
        $crawler = $this->client->submit($form, $params);
        
        $url = $this->router->generate('memoryWallDeleteMediaResource', array(
            'slug'  => 'my-memory-wall-2',
            'id'    => 'testMR',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue(strtolower($crawler->filter('h1')->text()) == 'delete an item from your memory wall');
    }
    
    public function testConfirmRemoveLastMediaResourceFromMemoryWallShowsWall(){
        
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser3',
            '_password' => 'testuser3',
        );
        $crawler = $this->client->submit($form, $params);
        
        $url = $this->router->generate('memoryWallDeleteMediaResourceConfirm', array(
            'slug'  => 'my-memory-wall-2',
            'id'    => 'testMR',
            'confirmed' => true,
        ));
        $crawler = $this->client->request('GET', $url);
        
        $this->assertTrue($crawler->filter('div.flashMessages li.notice')->count() > 0, 'flash messages');
        $this->assertTrue($crawler->filter('ul.userMemoryWalls li')->count() == 0, 'no media resources');
        $this->assertTrue($crawler->filter('h2:contains("Contents")')->siblings('p')->count() > 0, 'contents div is empty');
    }
    
    public function testRemoveOtherUsersMediaResourceFromMemoryWallThrowsException(){
        
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
        );
        $crawler = $this->client->submit($form, $params);
        
        $url = $this->router->generate('memoryWallDeleteMediaResourceConfirm', array(
            'slug'  => 'my-memory-wall',
            'id'    => 'testuser-mr2',
            'confirmed' => true,
        ));
        $crawler = $this->client->request('GET', $url);
        
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }
    
    public function testRemoveMediaResourceWhenNotLoggedInRedirectsToLogin(){
        $url = $this->router->generate('memoryWallDeleteMediaResource', array(
            'slug'  => 'my-memory-wall',
            'id'    => 'testuser-mr2',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->selectButton('Login')->count() > 0);
    }
    
    public function testRemoveMultipleReferencedMediaResourceFromWallOnlyRemovesSingleReference(){
        //if two users have the same media resource on their walls and one is removed, the other reference should remain in tact
        
    }
    
    
    
    
    
    
    
}

?>