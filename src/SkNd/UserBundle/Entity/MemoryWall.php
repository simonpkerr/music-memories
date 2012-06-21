<?php

namespace SkNd\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SkNd\UserBundle\Entity\User;
use SkNd\MediaBundle\Entity\Decade;
use SkNd\MediaBundle\Entity\MediaResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpKernel\Exception;

/**
 * SkNd\UserBundle\Entity\MemoryWall
 */
class MemoryWall
{
    public static $PRIVATE_WALLS = '0';
    public static $PUBLIC_WALLS = '1';
    public static $ALL_WALLS = '2';
    /**
     * @var integer $id
     */
    protected $id;

    protected $user;

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var string $slug
     */
    protected $slug;

    /**
     * @var string $description
     */
    protected $description;

    /**
     * @var datetime $dateCreated
     */
    protected $dateCreated;

    /**
     * @var boolean $isPublic
     */
    protected $isPublic;

    /**
     * @var datetime $lastUpdated
     */
    protected $lastUpdated;
    
    protected $associatedDecade;
    
    protected $mediaResources;
    
    protected $memoryWallMediaResources;

    public function __construct(User $user = null){
        $this->mediaResources = new ArrayCollection();
        $this->memoryWallMediaResources = new ArrayCollection();
        
        $this->setName('My Memory Wall');
        $this->setIsPublic(true);
        if($user != null)
            $this->setUser($user);
        
        //$this->mediaResources = $this->getMediaResources();
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
    
    public function getMediaResources(){
        //return $this->mediaResources;
        //return $this->memoryWallMediaResources;
        //$mrs = array();
        $mrs = new ArrayCollection();
        foreach($this->memoryWallMediaResources as $mwMr){
            $mrs->add($mwMr->getMediaResource());
        }
        return $mrs;
        
    }
    
    public function getMemoryWallMediaResources(){
        return $this->memoryWallMediaResources;                
    }
    
    public function addMediaResource(MediaResource $mr){
        $mwmrs = $this->getMemoryWallMediaResources();
        $mediaResourceExists = $mwmrs->filter(function($mwmr) use ($mr){
            return $mwmr->getMediaResource() == $mr && $mwmr->getMemoryWall() == $this;
        });
        
        if(!$mediaResourceExists){
            $mwMr = new MemoryWallMediaResource($this, $mr);        
            $this->memoryWallMediaResources->add($mwMr);
        }
        else
            throw new \RuntimeException ('Media Resource has already been added to this memory wall');
        
        
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }

    public function getDateCreated()
    {
        return $this->dateCreated;
    }
    
    
    public function setAssociatedDecade(Decade $associatedDecade)
    {
        $this->associatedDecade = $associatedDecade;
    }

    public function getAssociatedDecade()
    {
        return $this->associatedDecade;
    }
    

    /**
     * Set isPublic
     *
     * @param boolean $isPublic
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;
    }

    /**
     * Get isPublic
     *
     * @return boolean 
     */
    public function getIsPublic()
    {
        return $this->isPublic;
    }

    /**
     * Set lastUpdated
     *
     * @param datetime $lastUpdated
     */
    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;
    }

    /**
     * Get lastUpdated
     *
     * @return datetime 
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }
}