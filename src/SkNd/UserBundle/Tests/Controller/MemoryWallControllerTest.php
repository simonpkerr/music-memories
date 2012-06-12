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
    
    public function setup(){
        $this->client = static::createClient();
        $this->client->followRedirects(true);
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
    
    /**
     * @expectedException NotFoundHttpException
     */
    public function testMemoryWallIndexForNonexistentUserThrowsException(){
        $response = $this->client->request('GET', '/memorywalls/bogus_user/index');
    }
    
    public function testCreateMemoryWallWhenNotLoggedInRedirectsToLoginView(){
        
    }
    
    public function testCreateMemoryWallWhenAuthenticatedRedirectsToForm(){
        
    }
    
    public function testCreateMemoryWallWithMissingParametersShowsErrors(){
        
    }
    
    public function testCreateMemoryWallWithInvalidParametersShowsErrors(){
        
    }
    
    public function testCreateMemoryWallSuccessRedirectsToNewMemoryWall(){
        
    }
    
    
    
    
    
}
