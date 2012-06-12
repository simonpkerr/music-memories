<?php
namespace SkNd\UserBundle\Entity {
    
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
        $mw = new MemoryWall($this);
        $this->addMemoryWall($mw);
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

    
    public function addMemoryWall(MemoryWall $mw){
        $mw->setUser($this);
        $this->memoryWalls->add($mw);
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     */
    public function setFirstname($firstname = null)
    {
        $this->firstname = $firstname;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set surname
     *
     * @param string $surname
     */
    public function setSurname($surname = null)
    {
        $this->surname = $surname;
    }

    /**
     * Get surname
     *
     * @return string 
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set dateofbirth
     *
     * @param date $dateofbirth
     */
    public function setDateofbirth($dateofbirth)
    {
        $this->dateofbirth = $dateofbirth;
    }

    /**
     * Get dateofbirth
     *
     * @return date 
     */
    public function getDateofbirth()
    {
        return $this->dateofbirth;
    }
}
}
