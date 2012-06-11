<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * LoadUsers loads fixtures to test the User functionality
 * @author Simon Kerr
 * @version 1.0
 * to execute = php app/console doctrine:fixtures:load --fixtures=/path/to/fixture1 --fixtures=/path/to/fixture2 --append
 */
namespace SkNd\UserBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use SkNd\UserBundle\Entity\User;
use SkNd\UserBundle\Entity\MemoryWall;

class LoadUsers implements FixtureInterface, \Symfony\Component\DependencyInjection\ContainerAwareInterface {
    
    private $container; 
    private $em;
    private $userManager;
    
    //in order to get access to methods controlled by the container, it can be automatically injected using this method
    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container = null){
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getEntityManager();
        $this->userManager = $this->container->get('fos_user.user_manager');
    }
    
    public function load(ObjectManager $manager){
        //delete all records from the cached listings table first 
        $q = $this->em->createQuery('DELETE from SkNd\UserBundle\Entity\User');
        $q->execute();
        
        //3 sample users
        $user = $this->userManager->createUser();
        $user->setDateofbirth(new \DateTime("now"));
        $user->setUsername('testuser');
        $user->setEmail('test@test.com');
        $user->setPlainPassword('testuser');
        $user->setEnabled(true);
        //create a private memory wall 
        $privateMw = new MemoryWall($user);
        $privateMw->setIsPublic(false);
        $privateMw->setName('private wall');                
        $user->addMemoryWall($privateMw);
        $this->userManager->updateUser($user, true);
        
        //$manager->persist($user);
        
        $user = $this->userManager->createUser();
        $user->setDateofbirth(new \DateTime("now"));
        $user->setUsername('testuser2');
        $user->setEmail('test2@test.com');
        $user->setPlainPassword('testuser2');
        $user->setEnabled(true);
        $this->userManager->updateUser($user, true);
        
        $user = $this->userManager->createUser();
        $user->setDateofbirth(new \DateTime("now"));
        $user->setUsername('testuser3');
        $user->setEmail('test3@test3.com');
        $user->setPlainPassword('testuser3');
        $user->setEnabled(true);
        $this->userManager->updateUser($user, true);
        
    }
       
    
}

?>
