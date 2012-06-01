<?php

namespace SkNd\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * DefaultControllerTest for UserBundle tests all the 
 * functionality of the FOSUserBundle,
 * log in/out, register, update profile, change password etc
 * @author Simon Kerr
 * @version 1.0
 */


class DefaultControllerTest extends WebTestCase
{
    private $client;
    
    public function setup(){
        $this->client = static::createClient();
        $this->client->followRedirects(true);
        //$this->client->insulate();
    }
    
    //test all the routes    
    public function testLoginGetReturnsLoginScreen()
    {
        $crawler = $this->client->request('GET', '/login');
        $this->assertTrue($crawler->filter('input#username')->count() > 0);
    }
    
    //requires a user exist called testuser with a password of testuser
    public function testLoginPostReturnsToIndexWithCurrentUser()
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
            
        );
       
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('html:contains("testuser")')->count() > 0, "Failed to log in");
    }
    
    public function testLoginPostWithInvalidPasswordShowsErrors()
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'bogus_password',
            
        );
       
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('html:contains("The presented password is invalid")')->count() > 0);
    }
    
    public function testLoginPostWithMissingCredentialsShowsErrors()
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => '',
            '_password' => '',
            
        );
       
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('html:contains("Invalid username or password")')->count() > 0);
    }
    
        
    public function testRegisterGetReturnsRegisterScreen()
    {
        $crawler = $this->client->request('GET', '/register');
        $this->assertTrue($crawler->filter('input#fos_user_registration_form_username')->count() > 0);
    }
    
    public function testRegisterPostWithMissingUsernameShowsErrors(){
        $crawler = $this->client->request('GET', '/register');
        $crawler->selectButton('Register')->addContent('formnovalidate="formnovalidate"');
        $form = $crawler->selectButton('Register')->form();
        $params = array(
            'fos_user_registration_form[username]' => '',
            'fos_user_registration_form[email]' => 'n@dig.com',
      
        );
       
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('html:contains("Please enter a username")')->count() > 0);
    }
    
    public function testRegisterPostWithUsernameTooShortAndMissingEmailShowsErrors(){
        $crawler = $this->client->request('GET', '/register');
        $crawler->selectButton('Register')->addContent('formnovalidate="formnovalidate"');
        $form = $crawler->selectButton('Register')->form();
        $params = array(
            'fos_user_registration_form[username]' => 'a',
            'fos_user_registration_form[email]'    => ''
        );
       
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('html:contains("The username is too short")')->count() > 0);
        $this->assertTrue($crawler->filter('html:contains("Please enter an email")')->count() > 0);
    }
    
    
    
}
