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
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SkNd\UserBundle\Entity\User;
use SkNd\UserBundle\Entity\MemoryWall;
use SkNd\MediaBundle\Entity\MediaResource;
use SkNd\MediaBundle\Entity\MediaResourceCache;


class LoadUsers implements FixtureInterface, ContainerAwareInterface {
    
    private $container; 
    private $em;
    private $userManager;
    
    //in order to get access to methods controlled by the container, it can be automatically injected using this method
    public function setContainer(ContainerInterface $container = null){
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getEntityManager();
        $this->userManager = $this->container->get('fos_user.user_manager');
        
    }
    
    public function load(ObjectManager $manager){
        //$manager->createQuery('DELETE from SkNd\MediaBundle\Entity\MediaResource')->execute();
        $mrs = $manager->getRepository('SkNdMediaBundle:MediaResource')->findAll();
        foreach($mrs as $mr){
            $manager->remove($mr);
        }
        foreach($mrs = $manager->getRepository('SkNdMediaBundle:MediaResourceCache')->findAll() as $mrc)
            $manager->remove($mrc);
            
        $manager->flush();
        
        $users = $this->userManager->findUsers();
        foreach($users as $user){
            $this->userManager->deleteUser($user);
        }
             
        $mrs = array();
        array_push($mrs, $this->getNewMediaResource('testuser-mr1',$manager));
        array_push($mrs, $this->getNewMediaResource('testuser-mr2',$manager));
        array_push($mrs, $this->getNewMediaResource('testuser-mr3',$manager));
        
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
        $mw = $user->getMemoryWalls()->first();
        $mw->addMediaResource($mrs[0]);

        $user = $this->userManager->createUser();
        $user->setDateofbirth(new \DateTime("now"));
        $user->setUsername('testuser2');
        $user->setEmail('test2@test.com');
        $user->setPlainPassword('testuser2');
        $user->setEnabled(true);
        $this->userManager->updateUser($user, true);
        $mw = $user->getMemoryWalls()->first();
        $mw->addMediaResource($mrs[0]);
        $mw->addMediaResource($mrs[1]);
          
        $user = $this->userManager->createUser();
        $user->setDateofbirth(new \DateTime("now"));
        $user->setUsername('testuser3');
        $user->setEmail('test3@test3.com');
        $user->setPlainPassword('testuser3');
        $user->setEnabled(true);
        $this->userManager->updateUser($user, true);
        $mw = $user->getMemoryWalls()->first();
        $mw->addMediaResource($mrs[2]);
        
        $manager->flush();
        
    }
    
    private function getNewMediaResource($id, $manager){
        $mr = new MediaResource();
        $mr->setId($id);
        $mr->setAPI($manager->getRepository('SkNdMediaBundle:API')->findOneBy(array('id' => 1)));
        $mr->setMediaType($manager->getRepository('SkNdMediaBundle:MediaType')->findOneBy(array('id' => 1)));
        $mr->setMediaResourceCache($this->getCache($id));
        $manager->persist($mr);
        $manager->flush();
        
        return $mr;
    }
    private function getCache($id){
        $mrc = new MediaResourceCache();
        $mrc->setId($id);
        //$mrc->setDateCreated(new \DateTime("now"));
        $mrc->setTitle($id);
        $mrc->setXmlData(simplexml_load_file('src\SkNd\MediaBundle\Tests\MediaAPI\SampleResponses\sampleAmazonDetails.xml')->asXml());
        return $mrc;
    }
       
    
}

?>
