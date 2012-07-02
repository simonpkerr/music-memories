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
        $this->mediaapi = $kernel->getContainer()->get('sk_nd_media.mediaapi');
        $this->session = $this->mediaapi->getSession();
        $this->em = $this->mediaapi->getEntityManager();
    }
    
    private function getMediaSelection(){
        $crawler = $this->client->request('GET', '/index');
        $form = $crawler->selectButton('Search noodleDig')->form();
        $form['mediaSelection[decades]']->select('1');//all decades
        $form['mediaSelection[mediaTypes]']->select('1');//Film
        $form['mediaSelection[selectedMediaGenres]']->select('1');//All Genres
        $crawler = $this->client->submit($form);     
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
        $this->getMediaSelection();
        
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
        $this->getMediaSelection();
        
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
        $this->getMediaSelection();
        
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
        $this->getMediaSelection();
        
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
    
    /*public function testAddInvalidMediaResourceToValidMemoryWallThrowsException(){
        $testAmazonAPI = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\AmazonAPI')
                ->disableOriginalConstructor()
                ->setMethods(array(
                    'getDetails',
                ))
                ->getMock();
        $testAmazonAPI->expects($this->any())
                ->method('getDetails')
                ->will($this->returnValue(simplexml_load_file('src\SkNd\MediaBundle\Tests\MediaAPI\invalidSampleAmazonDetails.xml')));
  
    }*/

    public function testAddIdenticalMediaResourceTwiceToMemoryWallThrowsException(){
        $this->getMediaSelection();
        
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
        $this->assertTrue($crawler->filter('div#flashMessages li.notice')->count() > 0);
 
    }
    
    public function testMultipleUsersCanAddTheSameMediaResourceToTheirWalls(){
        $this->getMediaSelection();
        
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
        $this->getMediaSelection();
        
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
        $this->getMediaSelection();
        
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
        $this->getMediaSelection();
        
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
        
        $this->assertTrue($crawler->filter('div#flashMessages li.notice')->count() > 0, 'flash messages');
        $this->assertTrue($crawler->filter('ul.userMemoryWalls li')->count() == 0, 'no media resources');
        $this->assertTrue($crawler->filter('h2:contains("Contents")')->siblings('p')->count() > 0, 'contents div is empty');
    }
    
    public function testRemoveOtherUsersMediaResourceFromMemoryWallThrowsException(){
        $this->getMediaSelection();
        
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
    
    public function testDeleteMemoryWallAlsoRemovesAssociatedMediaResources(){
        $this->getMediaSelection();
        
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
            'id'    => 'newMR'
        ));
        $crawler = $this->client->request('GET', $url);
        
        $url = $this->router->generate('memoryWallDelete', array('slug' => 'my-memory-wall-2'));
        $crawler = $this->client->request('GET', $url);

        $url = $this->router->generate('memoryWallDeleteConfirm', array('slug' => 'my-memory-wall-2'));
        $crawler = $this->client->request('GET', $url);
        
        $this->assertTrue($crawler->filter('ul#memoryWallGallery dd')->eq(4)->text() == '0');
    }
    
    
    
    
    
}

?>