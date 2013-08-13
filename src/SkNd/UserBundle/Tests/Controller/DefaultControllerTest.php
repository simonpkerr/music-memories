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

class DefaultControllerTest extends WebTestCase
{
    private $client;
    protected static $kernel;
    protected static $em;
    
    public static function setUpBeforeClass(){
        self::$kernel = static::createKernel();
        self::$kernel->boot();
        self::$em = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        
        $loadUsers = new \SkNd\UserBundle\DataFixtures\ORM\LoadUsers();
        $loadUsers->setContainer(self::$kernel->getContainer());
        $loadUsers->load(self::$em);
    }
    
    public static function tearDownAfterClass(){
        self::$kernel = null;
        self::$em = null;
    }
    
    public function setup(){
        $this->client = static::createClient();
        $this->client->followRedirects(true);
    }
    
    public function tearDown(){
        unset($this->client);
    }
    
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
        $this->assertTrue($crawler->filter('html:contains("Invalid username or password")')->count() > 0);
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
        //below line removes the html5 client side validation from the submit button to check server side validation
        $crawler->selectButton('Get noodling')->addContent('formnovalidate="formnovalidate"');
        $form = $crawler->selectButton('Get noodling')->form();
        $params = array(
            'fos_user_registration_form[username]' => '',
            'fos_user_registration_form[email]' => 'n@dig.com',
      
        );
       
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('html:contains("Please enter a username")')->count() > 0);
    }
    
    public function testRegisterPostWithUsernameTooShortAndMissingEmailShowsErrors(){
        $crawler = $this->client->request('GET', '/register');
        $crawler->selectButton('Get noodling')->addContent('formnovalidate="formnovalidate"');
        $form = $crawler->selectButton('Get noodling')->form();
        $params = array(
            'fos_user_registration_form[username]' => 'a',
            'fos_user_registration_form[email]'    => ''
        );
       
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('html:contains("The username is too short")')->count() > 0);
        $this->assertTrue($crawler->filter('html:contains("Please enter an email")')->count() > 0);
    }
    
    public function testRegisterWithValidCredentialsCreatesRandomMemoryWallUsingUserDetails(){
        $crawler = $this->client->request('GET', '/register');
        $form = $crawler->selectButton('Get noodling')->form();
        $params = array(
            'fos_user_registration_form[username]'              => 'testUserCreateWall',
            'fos_user_registration_form[email]'                 => 'testUserCreateWall@testUserCreateWall.com',
            'fos_user_registration_form[plainPassword][first]'  => 'testUserCreateWall',
            'fos_user_registration_form[plainPassword][second]' => 'testUserCreateWall',
        );
        
        //NEED TO TICK TAC AGREEMENT
        $form['fos_user_registration_form[tacagreement]']->tick();
       
        $crawler = $this->client->submit($form, $params);
        $crawler = $this->client->request('GET', '/memorywalls/personal/index');
        
        $this->assertTrue($crawler->filter('ul#memoryWallGallery li:first-child > div:contains("testUserCreateWall")')->count() > 0);
    }
    
    //for each user, a random avatar is generated (bottle cap, random object from db)
    public function testRegisterWithValidCredentialsSelectsRandomAvatar(){
        
    }
    
    public function testResetPasswordWithInvalidCredentialsShowsErrors(){
        $crawler = $this->client->request('GET', '/resetting/request');
        $form = $crawler->selectButton('Reset password')->form();
        $params = array(
            'username' => 'bogus_username',
        );
       
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('html:contains("does not exist.")')->count() > 0);
    }
    
    //requires a user exist called testuser with a password of testuser
    public function testUpdateProfileWithInvalidCredentialsShowsErrors(){
        //log in first
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
            
        );
        $crawler = $this->client->submit($form, $params);
        $profileLink = $crawler->selectLink('Profile')->link();
        $crawler = $this->client->click($profileLink);
        
        $crawler->selectButton('Update')->addContent('formnovalidate="formnovalidate"');
        $form = $crawler->selectButton('Update')->form();
        $params = array(
            'fos_user_profile_form[username]'           => '',
            'fos_user_profile_form[email]'              => 'invalid_email',
            'fos_user_profile_form[firstname]'          => 'u',
            'fos_user_profile_form[current_password]'   => 'wrong_password',
        );
       
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('html:contains("Please enter a username")')->count() > 0, "no username specified");
        $this->assertTrue($crawler->filter('html:contains("The email is not valid")')->count() > 0, "invalid email given");
        $this->assertTrue($crawler->filter('html:contains("This firstname is too short")')->count() > 0, "first name is too short");
        $this->assertTrue($crawler->filter('html:contains("This value should be the user current password")')->count() > 0, "invalid password");
        
    }
    
    //requires a user exist called testuser with a password of testuser
    public function testUpdateProfileWithCorrectCredentialsAndNewFirstNameShowsProfile(){
        //log in first
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
        );
        $crawler = $this->client->submit($form, $params);
        $profileLink = $crawler->selectLink('Profile')->link();
        $crawler = $this->client->click($profileLink);
        
        $crawler->selectButton('Update')->addContent('formnovalidate="formnovalidate"');
        $form = $crawler->selectButton('Update')->form();
        $params = array(
            'fos_user_profile_form[firstname]'          => 'Simon',
            'fos_user_profile_form[current_password]'   => 'testuser',
        );
       
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('html:contains("The profile has been updated")')->count() > 0, "profile unsuccessfully updated.");
        
    }
    
    //for some reason phpunit crashes here
    public function testChangePasswordWithDifferentPasswordsShowsErrors(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
            
        );
        $crawler = $this->client->submit($form, $params);
        
        //go to the profile page
        $crawler = $this->client->request('GET', '/profile/change-password');
        $form = $crawler->selectButton('Change password')->form();
        $params = array(
            'fos_user_change_password_form[current_password]' => 'testuser',
            'fos_user_change_password_form[new][first]' => 'testuser_new',
            'fos_user_change_password_form[new][second]' => 'testuser_invalid',
            
        );
        $crawler = $this->client->submit($form, $params);
        //indicates a flash message saying password is not valid
        $this->assertTrue($crawler->filter('html:contains("The entered passwords don\'t match")')->count() > 0);
    }
    
    public function testChangePasswordWhenNotLoggedInRedirectsToLoginPage(){
        $crawler = $this->client->request('GET', '/profile/change-password');
        
        $this->assertTrue($crawler->selectButton("Login")->count() > 0, "redirected to log in screen");
        
    }    
    
    public function testChangePasswordWithCorrectDataShowsProfile(){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser3',
            '_password' => 'testuser3',
            
        );
        $crawler = $this->client->submit($form, $params);
        $crawler = $this->client->request('GET', '/profile/change-password');
        
        $form = $crawler->selectButton('Change password')->form();
        $params = array(
            'fos_user_change_password_form[current_password]'   => 'testuser3',
            'fos_user_change_password_form[new][first]'         => 'testuser3_new',
            'fos_user_change_password_form[new][second]'        => 'testuser3_new',
            
        );
        $crawler = $this->client->submit($form, $params);
        
        $this->assertTrue($crawler->filter('html:contains("The password has been changed")')->count() > 0, "Password changed.");
        
    }
    
    
    
    
}
