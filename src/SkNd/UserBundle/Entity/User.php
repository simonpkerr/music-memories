<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * User gets and sets data for the named entity.
 * It extends the FOSUserBundle User entity but adds firstname, surname and date of birth
 * @author Simon Kerr
 * @version 1.0
 */
namespace SkNd\UserBundle\Entity;
    
use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use SkNd\UserBundle\Entity\MemoryWall;
use Doctrine\Common\Collections\ArrayCollection;

class User extends BaseUser {

    protected $id;
    protected $firstname;
    protected $surname;
    protected $dateofbirth;
    protected $memoryWalls;

    public function __construct(){
        $this->memoryWalls = new ArrayCollection();
        $this->createDefaultMemoryWall();
        parent::__construct();
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

    public function setFirstname($firstname = null)
    {
        $this->firstname = $firstname;
    }

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function setSurname($surname = null)
    {
        $this->surname = $surname;
    }

    public function getSurname()
    {
        return $this->surname;
    }

    public function setDateofbirth($dateofbirth)
    {
        $this->dateofbirth = $dateofbirth;
    }

    public function getDateofbirth()
    {
        return $this->dateofbirth;
    }

}
