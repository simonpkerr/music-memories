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
        //below line removes the html5 client side validation from the submit button to check server side validation
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
        $profileLink = $crawler->selectLink('Manage your account')->link();
        $crawler = $this->client->click($profileLink);
        
        $crawler->selectButton('Update your profile')->addContent('formnovalidate="formnovalidate"');
        $form = $crawler->selectButton('Update your profile')->form();
        $params = array(
            'fos_user_profile_form[user][username]' => '',
            'fos_user_profile_form[user][email]'    => 'invalid_email',
            'fos_user_profile_form[user][firstname]'=> 'u',
            'fos_user_profile_form[current]'        => 'wrong_password',
        );
       
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('html:contains("Please enter a username")')->count() > 0, "no username specified");
        $this->assertTrue($crawler->filter('html:contains("The email is not valid")')->count() > 0, "invalid email given");
        $this->assertTrue($crawler->filter('html:contains("This firstname is too short")')->count() > 0, "first name is too short");
        $this->assertTrue($crawler->filter('html:contains("This password is invalid")')->count() > 0, "invalid password");
        
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
        $profileLink = $crawler->selectLink('Manage your account')->link();
        $crawler = $this->client->click($profileLink);
        
        $crawler->selectButton('Update your profile')->addContent('formnovalidate="formnovalidate"');
        $form = $crawler->selectButton('Update your profile')->form();
        $params = array(
            'fos_user_profile_form[user][firstname]'=> 'Simon',
            'fos_user_profile_form[current]'        => 'testuser',
        );
       
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('html:contains("Your profile has been updated")')->count() > 0, "profile successfully updated.");
        
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
        $crawler = $this->client->request('GET', '/change-password/change-password');
        $form = $crawler->selectButton('Change password')->form();
        $params = array(
            'fos_user_change_password_form[current]' => 'testuser',
            'fos_user_change_password_form[new][first]' => 'testuser_new',
            'fos_user_change_password_form[new][second]' => 'testuser_invalid',
            
        );
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('html:contains("Please enter a new password")')->count() > 0);
        
    }
    
    public function testChangePasswordWhenNotLoggedInRedirectsToLoginPage(){
        $crawler = $this->client->request('GET', '/change-password/change-password');
        
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
        $crawler = $this->client->request('GET', '/change-password/change-password');
        
        $form = $crawler->selectButton('Change password')->form();
        $params = array(
            'fos_user_change_password_form[current]'        => 'testuser3',
            'fos_user_change_password_form[new][first]'     => 'testuser3_new',
            'fos_user_change_password_form[new][second]'    => 'testuser3_new',
            
        );
        $crawler = $this->client->submit($form, $params);
        
        $this->assertTrue($crawler->filter('html:contains("The password has been changed")')->count() > 0, "Password changed.");
        
    }
    
    
    
    
}
