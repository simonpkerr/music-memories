<?php

namespace SkNd\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use SkNd\MediaBundle\MediaAPI\MediaAPI;
use SkNd\MediaBundle\Entity\MediaSelection;
use SkNd\MediaBundle\Entity\MediaResource;

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MemoryWallControllerTest for all operations of memory walls 
 * the DataFixtures/ORM/LoadUsers fixtures file should be loaded first
 * @author Simon Kerr
 * @version 1.0
 * debug cmd: set XDEBUG_CONFIG=idekey=netbeans-xdebug 
 */


class MemoryWallControllerTest extends WebTestCase
{
    private $client;
    protected static $em;
    protected static $kernel;
    protected static $router;
    
    public static function setUpBeforeClass(){
        self::$kernel = static::createKernel();
        self::$kernel->boot();
        self::$em = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        self::$router = self::$kernel->getContainer()->get('router');
        
        $loadUsers = new \SkNd\UserBundle\DataFixtures\ORM\LoadUsers();
        $loadUsers->setContainer(self::$kernel->getContainer());
        $loadUsers->load(self::$em);
    }
    
    public static function tearDownAfterClass(){
        self::$kernel = null;
        self::$em = null;
        self::$router = null;
    }
        
    
    public function setUp(){
        $this->client = static::createClient();
        $this->client->followRedirects(true);
    }
    
    public function tearDown(){
        unset($this->client);
    }
   

    public function testMemoryWallIndexWithNoParamsShowsPublicWallsForNonLoggedInUser(){
        //create the user fixtures, which by default creates memory walls
        $crawler = $this->client->request('GET', '/memorywalls/public/index');
        $this->assertTrue($crawler->filter('h1')->text() == 'All Public Memory Walls', "showing all public walls");       
        $this->assertTrue($crawler->filter('private wall')->count() == 0, "not showing private walls");       
    }
    
    public function testMemoryWallIndexWithUsernameShowsPublicWallsForGivenUserWhenUserNotLoggedIn(){
        $crawler = $this->client->request('GET', '/memorywalls/testuser/index');
        $this->assertTrue($crawler->filter('h1')->text() == htmlentities("testuser's Memory Walls"), "showing other users memory walls");       
        $this->assertTrue($crawler->filter('private wall')->count() == 0, "private walls not available");       
    }
    
    public function testPersonalMemoryWallIndexWithUsernameShowsPublicAndPrivateWallsWhenUserIsAuthenticated(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
            
        );       
        $crawler = $this->client->submit($form, $params);
        
        $crawler = $this->client->request('GET', '/memorywalls/personal/index');
        $this->assertTrue($crawler->filter('h1')->text() == 'My Memory Walls', "showing my memory walls");       
        //$this->assertTrue($crawler->filter('body > ul li')->eq(1)->filter('dl dd')->eq(0)->text() == 'private wall', "showing private walls");       
        $this->assertTrue($crawler->filter('ul#memoryWallGallery:contains("private wall")')->count() > 0, "not showing private walls");       
    }
    
    public function testMemoryWallIndexForNonexistentUserThrowsException(){
        $this->client->request('GET', '/memorywalls/bogus_user/index');
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }
    
    public function testShowMemoryWallForNonExistentWallThrowsException(){
        $url = self::$router->generate('memoryWallShow', 
                array(
                    'id' => 1,
                    'slug' => 'bogus-wall')
                );
        $this->client->request('GET', $url);
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }
    
    public function testShowPrivateWallWhenNotLoggedInRedirectsToLogin(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('private-wall');
        
        $url = self::$router->generate('memoryWallShow', array('id' => $mw->getId(), 'slug' => 'private-wall'));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->selectButton('Login')->count() > 0);
    }
    
    public function testShowPrivateWallNotBelongingToLoggedInUserThrowsException(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser2',
            '_password' => 'testuser2',
            
        );       
        
        $crawler = $this->client->submit($form, $params);
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('private-wall');
        $url = self::$router->generate('memoryWallShow', array('id' => $mw->getId(), 'slug' => 'private-wall'));
        $this->client->request('GET', $url);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
    
    public function testCreateMemoryWallWhenNotLoggedInRedirectsToLogin(){
        $url = self::$router->generate('memoryWallCreate');
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertTrue($crawler->selectButton('Login')->count() > 0);
    }
    
    public function testCreateMemoryWallWhenAuthenticatedRedirectsToForm(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser2',
            '_password' => 'testuser2',
            
        );       
        $crawler = $this->client->submit($form, $params);
        $url = self::$router->generate('memoryWallCreate');
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->selectButton('Make it, baby!')->count() > 0, "create memory wall page not shown");
    }
    
    public function testCreateMemoryWallWithMissingParametersShowsErrors(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser2',
            '_password' => 'testuser2',
            
        );       
        $crawler = $this->client->submit($form, $params);
        $url = self::$router->generate('memoryWallCreate');
        $crawler = $this->client->request('GET', $url);
        
        $crawler->selectButton('Make it, baby!')->addContent('formnovalidate="formnovalidate"');
        $form = $crawler->selectButton('Make it, baby!')->form();
        $params = array(
            'memoryWall[name]' => '',
        );
       
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('ul.form-errors li')->count() > 0, "no errors for missing name field");
        
    }
    
    public function testCreateMemoryWallWithInvalidParametersShowsErrors(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser2',
            '_password' => 'testuser2',
            
        );       
        $crawler = $this->client->submit($form, $params);
        $url = self::$router->generate('memoryWallCreate');
        $crawler = $this->client->request('GET', $url);
        
        $crawler->selectButton('Make it, baby!')->addContent('formnovalidate="formnovalidate"');
        $form = $crawler->selectButton('Make it, baby!')->form();
        $params = array(
            'memoryWall[name]'        => 'a',
            'memoryWall[description]' => 'a',
        );
       
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('ul.form-errors li')->count() > 0);
    }
    
    public function testCreateMemoryWallSuccessRedirectsToNewMemoryWall(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser2',
            '_password' => 'testuser2',
            
        );       
        $crawler = $this->client->submit($form, $params);
        $url = self::$router->generate('memoryWallCreate');
        $crawler = $this->client->request('GET', $url);
        
        $crawler->selectButton('Make it, baby!')->addContent('formnovalidate="formnovalidate"');
        $form = $crawler->selectButton('Make it, baby!')->form();
        $params = array(
            'memoryWall[name]'        => 'test memory wall',
        );
       
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('h1')->text() == 'Test memory wall', "Wall not created successfully");
        
    }
    
    public function testCreateMemoryWallUsesFirstnameIfAvailable(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
            
        );       
        $crawler = $this->client->submit($form, $params);
        $url = self::$router->generate('memoryWallCreate');
        $crawler = $this->client->request('GET', $url);
        
        $form = $crawler->selectButton('Make it, baby!')->form();
        $params = array();
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('h1:contains("Testuserfirstnames")')->count() > 0, "Wall name not using users first name");
    }
    
    public function testCreateMemoryWallUsesUsernameIfNecessary(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser2',
            '_password' => 'testuser2',
            
        );       
        $crawler = $this->client->submit($form, $params);
        $url = self::$router->generate('memoryWallCreate');
        $crawler = $this->client->request('GET', $url);
        
        $form = $crawler->selectButton('Make it, baby!')->form();
        $params = array();
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('h1:contains("Testuser2s")')->count() > 0, "Wall name not using users username");
    }
    
    //create a load of custom avatars that the user can choose from (based on decades) with an option to upload a new one
    public function testCreateMemoryWallUsesUserCustomAvatarIfAvailable() {
        
    }
    
    public function testCreateMemoryWallUsesUserDefaultAvatarIfNecessary(){
        
    }
    
    public function testEditNonExistentMemoryWallThrowsException(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
            
        );       
        $crawler = $this->client->submit($form, $params);
        $url = self::$router->generate('memoryWallEdit', array('id' => 1, 'slug' => 'bogus-wall'));
        $crawler = $this->client->request('GET', $url);
        
        $this->assertTrue($this->client->getResponse()->isNotFound());
    
    }
    
    public function testEditOtherUsersMemoryWallThrowsException(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
        );       
        $crawler = $this->client->submit($form, $params);
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-1');
        $url = self::$router->generate('memoryWallEdit', array('id' => $mw->getId(), 'slug' => 'my-memory-wall-1'));
        $crawler = $this->client->request('GET', $url);
        
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
    
    public function testEditMemoryWallWithInvalidParametersShowsErrors(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
            
        );       
        $crawler = $this->client->submit($form, $params);
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall');
        $url = self::$router->generate('memoryWallEdit', array('id' => $mw->getId(), 'slug' => 'my-memory-wall'));
        $crawler = $this->client->request('GET', $url);
        
        $crawler->selectButton('Update this wall')->addContent('formnovalidate="formnovalidate"');
        $form = $crawler->selectButton('Update this wall')->form();
        $params = array(
            'memoryWall[name]'        => 'a',
            'memoryWall[description]' => 'a',
        );
       
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('ul.form-errors li')->count() > 0);
    }
    
    public function testEditMemoryWallWithMissingParametersShowsErrors(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
            
        );       
        $crawler = $this->client->submit($form, $params);
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall');
        $url = self::$router->generate('memoryWallEdit', array('id' => $mw->getId(), 'slug' => 'my-memory-wall'));
        $crawler = $this->client->request('GET', $url);
        
        $crawler->selectButton('Update this wall')->addContent('formnovalidate="formnovalidate"');
        $form = $crawler->selectButton('Update this wall')->form();
        $params = array(
            'memoryWall[name]'        => '',
        );
       
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('ul.form-errors li')->count() > 0);
    }
    
    public function testEditMemoryWallSuccessShowsMemoryWall(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
            
        );       
        $crawler = $this->client->submit($form, $params);
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall');
        $url = self::$router->generate('memoryWallEdit', array('id' => $mw->getId(), 'slug'   =>  'my-memory-wall'));
        $crawler = $this->client->request('GET', $url);
        
        $form = $crawler->selectButton('Update this wall')->form();
        $params = array(
            'memoryWall[description]'   =>  'a new description for the wall',
        );
        
               
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('h1:contains("'. ucfirst($mw->getName()) .'")')->count() > 0);
                          
    }
    
    public function testDeleteNonExistentWallThrowsException(){        
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
            
        );       
        $crawler = $this->client->submit($form, $params);
        $url = self::$router->generate('memoryWallDelete', array('id' => 1, 'slug' => 'non-existent-wall'));
        $crawler = $this->client->request('GET', $url);
        
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }
    
    public function testDeleteOtherUsersWallWhenLoggedInThrowsException(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
            
        );       
        $crawler = $this->client->submit($form, $params);
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-1');
        $url = self::$router->generate('memoryWallDelete', array('id' => $mw->getId(), 'slug' => 'my-memory-wall-1'));
        $crawler = $this->client->request('GET', $url);
        
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
    
    public function testDeleteConfirmOtherUsersWallWhenLoggedInThrowsException(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
            
        );       
        $crawler = $this->client->submit($form, $params);
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-1');
        $url = self::$router->generate('memoryWallDeleteConfirm', array('id' => $mw->getId(), 'slug' => 'my-memory-wall-1'));
        $crawler = $this->client->request('GET', $url);
        
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }

    public function testDeleteConfirmOwnWallDirectlyThrowsException(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
            
        );       
        $crawler = $this->client->submit($form, $params);
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall');
        $url = self::$router->generate('memoryWallDeleteConfirm', array('id' => $mw->getId(), 'slug' => 'my-memory-wall'));
        $crawler = $this->client->request('GET', $url);
        
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }
    
    public function testDeleteWallWhenNotLoggedInRedirectsToLogin(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-1');
        $url = self::$router->generate('memoryWallDelete', array('id' => $mw->getId(), 'slug' => 'my-memory-wall-1'));
        $crawler = $this->client->request('GET', $url);
        
        $this->assertTrue($crawler->selectButton('Login')->count() > 0);
    }
    
    public function testDeleteWallConfirmDeleteRedirectsToPersonalWallIndexWithMessage(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
            
        );       
        $crawler = $this->client->submit($form, $params);
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('private-wall');
        
        $url = self::$router->generate('memoryWallDelete', array('id' => $mw->getId(), 'slug' => 'private-wall'));
        $crawler = $this->client->request('GET', $url);

        $url = self::$router->generate('memoryWallDeleteConfirm', array('id' => $mw->getId(), 'slug' => 'private-wall'));
        $crawler = $this->client->request('GET', $url);
        
        $this->assertTrue($crawler->filter('div.flashMessages:contains("Memory wall")')->count() > 0, "memory wall not deleted");
        
        //add wall again
        /*$url = self::$router->generate('memoryWallCreate');
        $crawler = $this->client->request('GET', $url);
        
        $crawler->selectButton('Create a new Memory Wall')->addContent('formnovalidate="formnovalidate"');
        $form = $crawler->selectButton('Create a new Memory Wall')->form();
        $params = array(
            'memoryWall[name]'        => 'Private Wall',
        );
        $crawler = $this->client->submit($form, $params);*/
        
    }
    
    public function testDeleteLastMemoryWallCreatesNewDefaultWall(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
            
        );       
        $crawler = $this->client->submit($form, $params);
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall');
        
        $url = self::$router->generate('memoryWallDelete', array('id' => $mw->getId(), 'slug' => 'my-memory-wall'));
        $crawler = $this->client->request('GET', $url);

        $url = self::$router->generate('memoryWallDeleteConfirm', array('id' => $mw->getId(), 'slug' => 'my-memory-wall'));
        $crawler = $this->client->request('GET', $url);
        
        $this->assertTrue($crawler->filter('div.flashMessages ul li:contains("That was your last Memory Wall")')->count() > 0);
        $this->assertTrue($crawler->filter('ul#memoryWallGallery:contains("testuser")')->count() > 0);
    }
    
    public function testDeleteMemoryWallAlsoRemovesAssociatedMediaResources(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser3',
            '_password' => 'testuser3',
            
        ); 
        $crawler = $this->client->submit($form, $params);
        
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-2');
        $url = self::$router->generate('memoryWallAddMediaResource', array(
            'mwid'  => $mw->getId(),
            'slug'  => 'my-memory-wall-2',
            'api'   => 'amazonapi',
            'id'    => 'newMR',
            'title' => 'some-resource',
        ));
        $crawler = $this->client->request('GET', $url);
        
        $url = self::$router->generate('memoryWallDelete', array('id' => $mw->getId(), 'slug' => 'my-memory-wall-2'));
        
        $crawler = $this->client->request('GET', $url);

        $url = self::$router->generate('memoryWallDeleteConfirm', array('id' => $mw->getId(), 'slug' => 'my-memory-wall-2'));
        $crawler = $this->client->request('GET', $url);
        
        $this->assertTrue($crawler->filter('ul#memoryWallGallery li:first-child span.note:contains("0 items")')->count() > 0);
    }
    
    //layout methods
    public function testChangeLayoutToGridUpdatesWallLayout(){
        
    }
    
    public function testChangeLayoutToCustomUpdatesWallLayoutAndMovesItems(){
        
    }
    
    public function testChangeLayoutToCustomUpdatesWallLayoutAndMovesItemsToDefaultLocationsIfNoCoordsSpecified(){
        
    }
    
    public function testResetCustomViewRevertsToGridLayout(){
        
    }
    
    
}
