<?php

namespace SkNd\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
    private $router;
    
    public function setup(){
        $this->client = static::createClient();
        $this->client->followRedirects(true);
        $kernel = static::createKernel();
        $kernel->boot();
        $this->router = $kernel->getContainer()->get('router');
        
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
        $this->assertTrue($crawler->filter('body > ul li')->eq(1)->filter('dd')->text() == 'private wall', "showing private walls");       
    }
    
    public function testMemoryWallIndexForNonexistentUserThrowsException(){
        $this->client->request('GET', '/memorywalls/bogus_user/index');
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }
    
    public function testShowMemoryWallForNonExistentWallThrowsException(){
        $url = $this->router->generate('memoryWallShow', array('slug' => 'bogus-wall'));
        $this->client->request('GET', $url);
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }
    
    public function testShowPrivateWallWhenNotLoggedInRedirectsToLogin(){
        $url = $this->router->generate('memoryWallShow', array('slug' => 'private-wall'));
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
        $url = $this->router->generate('memoryWallShow', array('slug' => 'private-wall'));
        $this->client->request('GET', $url);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
    
    public function testCreateMemoryWallWhenNotLoggedInRedirectsToLogin(){
        $url = $this->router->generate('memoryWallCreate');
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
        $url = $this->router->generate('memoryWallCreate');
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->selectButton('Create a new Memory Wall')->count() > 0, "Add memory wall form exists");
    }
    
    public function testCreateMemoryWallWithMissingParametersShowsErrors(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser2',
            '_password' => 'testuser2',
            
        );       
        $crawler = $this->client->submit($form, $params);
        $url = $this->router->generate('memoryWallCreate');
        $crawler = $this->client->request('GET', $url);
        
        $crawler->selectButton('Create a new Memory Wall')->addContent('formnovalidate="formnovalidate"');
        $form = $crawler->selectButton('Create a new Memory Wall')->form();
        $params = array(
            'memoryWall[name]' => '',
        );
       
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('ul.form-errors li')->count() > 0, "Errors show for missing name field");
        
    }
    
    public function testCreateMemoryWallWithInvalidParametersShowsErrors(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser2',
            '_password' => 'testuser2',
            
        );       
        $crawler = $this->client->submit($form, $params);
        $url = $this->router->generate('memoryWallCreate');
        $crawler = $this->client->request('GET', $url);
        
        $crawler->selectButton('Create a new Memory Wall')->addContent('formnovalidate="formnovalidate"');
        $form = $crawler->selectButton('Create a new Memory Wall')->form();
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
        $url = $this->router->generate('memoryWallCreate');
        $crawler = $this->client->request('GET', $url);
        
        $crawler->selectButton('Create a new Memory Wall')->addContent('formnovalidate="formnovalidate"');
        $form = $crawler->selectButton('Create a new Memory Wall')->form();
        $params = array(
            'memoryWall[name]'        => 'test memory wall',
        );
       
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('h1')->text() == 'test memory wall', "Wall created successfully");
        
    }
    
    public function testEditNonExistentMemoryWallThrowsException(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
            
        );       
        $crawler = $this->client->submit($form, $params);
        $url = $this->router->generate('memoryWallEdit', array('slug' => 'bogus-wall'));
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
        $url = $this->router->generate('memoryWallEdit', array('slug' => 'test-memory-wall-2'));
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
        $url = $this->router->generate('memoryWallEdit', array('slug'   =>  'my-memory-wall'));
        $crawler = $this->client->request('GET', $url);
        
        $crawler->selectButton('Edit this wall')->addContent('formnovalidate="formnovalidate"');
        $form = $crawler->selectButton('Edit this wall')->form();
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
        $url = $this->router->generate('memoryWallEdit', array('slug'   =>  'my-memory-wall'));
        $crawler = $this->client->request('GET', $url);
        
        $crawler->selectButton('Edit this wall')->addContent('formnovalidate="formnovalidate"');
        $form = $crawler->selectButton('Edit this wall')->form();
        $params = array(
            'memoryWall[name]'        => '',
        );
       
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('ul.form-errors li')->count() > 0);
    }
    
    public function testEditMemoryWallSuccessShowsMemoryWall(){
        
    }
    
    public function testDeleteOtherUsersWallWhenLoggedInThrowsException(){
        
    }
    
    public function testDeleteWallWhenNotLoggedInRedirectsToLogin(){
        
    }
    
    public function testDeleteWallConfirmDeleteRedirectsToPersonalWallIndexWithMessage(){
        
    }
    
    public function testAddMediaResourceToMemoryWallWhenNotLoggedInRedirectsToLogin(){
        
    }
    
    public function testAddMediaResourceToAccountWithNoMemoryWallRedirectsToCreateMemoryWall(){
        
    }
    
    public function testAddMediaResourceToMemoryWallShowsMemoryWallWithResource(){
        
    }
    
    
    
    
    
    
}
