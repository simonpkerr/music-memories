<?php

namespace SkNd\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * DefaultControllerTest for UserBundle tests all the 
 * functionality of the FOSUserBundle,
 * log in/out, register, update profile, change password etc
 * the DataFixtures/ORM/LoadUsers fixtures file should be loaded first
 * @author Simon Kerr
 * @version 1.0
 */


class MemoryWallControllerTest extends WebTestCase
{
    private $client;
    
    public function setup(){
        $this->client = static::createClient();
        $this->client->followRedirects(true);
    }
    
    public function testMemoryWallIndexWithNoParamsShowsPublicWallsForNonLoggedInUser(){
        
    }
    
    public function testMemoryWallIndexWithUsernameShowsPublicWallsForGivenUserWhenUserNotLoggedIn(){
        
    }
    
    public function testPersonalMemoryWallIndexWithUsernameShowsPublicAndPrivateWallsWhenUserIsAuthenticated(){
        
    }
    
    public function testMemoryWallIndexForNonexistentUserThrowsException(){
        
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
