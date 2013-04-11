<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * User gets and sets data for the named entity.
 * It extends the FOSUserBundle User entity but adds firstname, surname and date of birth
 * @author Simon Kerr
 * @version 1.0
 */
namespace SkNd\UserBundle\Entity;
    
//use FOS\UserBundle\Entity\User as BaseUser;
use Sonata\UserBundle\Entity\BaseUser as BaseUser;
use SkNd\UserBundle\Entity\MemoryWall;
use Doctrine\Common\Collections\ArrayCollection;

class User extends BaseUser {

    protected $id;
    /*protected $firstname;
    protected $lastname;
    protected $dateOfBirth;*/
    protected $memoryWalls;
    protected $tacagreement;
    //const ND_ROLE = 'ND_USER';

    public function __construct(){
        parent::__construct();
        //$this->addRole(self::ND_ROLE);
        $this->memoryWalls = new ArrayCollection();
        $this->createDefaultMemoryWall();
    }
    
    public function getMemoryWalls($includePrivateWalls = true){
        if($includePrivateWalls)
            return $this->memoryWalls;
        else{
            
            return $this->memoryWalls->filter(function($mw){
                return $mw->getIsPublic() === true;
            });
        }
    }
    
    public function createDefaultMemoryWall(){
        $mw = new MemoryWall($this);
        $this->addMemoryWall($mw);
    }

    
    public function addMemoryWall(MemoryWall $mw){
        $mw->setUser($this);
        $this->memoryWalls->add($mw);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTacagreement($tacagreement){
        $this->tacagreement = $tacagreement;
    }
    
    public function getTacagreement(){
        return $this->tacagreement;
    }
    
   /* public function setFirstname($firstname = null)
    {
        $this->firstname = $firstname;
    }

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function setLastname($lastname = null)
    {
        $this->lastname = $lastname;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function setDateOfbirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    public function getDateOfbirth()
    {
        return $this->dateOfBirth;
    }*/

}
