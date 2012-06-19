<?php

namespace SkNd\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SkNd\UserBundle\Entity\MemoryWall;
use SkNd\MediaBundle\Entity\MediaResource;
use Doctrine\Common\Collections\ArrayCollection;

class MemoryWallMediaResource
{
    protected $id;
    
    protected $memoryWall;

    protected $mediaResource;

    protected $userTitle;

    protected $slug;
    
    protected $dateAdded;
    
    /*public function __construct(){
        $this->memoryWall = new ArrayCollection();
        $this->mediaResource = new ArrayCollection();
    }*/
     
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }

    public function setMemoryWall(MemoryWall $memoryWall)
    {
        $this->memoryWall = $memoryWall;
    }

    public function getMemoryWall()
    {
        return $this->memoryWall;
    }

    public function setMediaResource(MediaResource $mediaResource)
    {
        $this->mediaResource = $mediaResource;
    }

    public function getMediaResource()
    {
        return $this->mediaResource;
    }

    /**
     * Set userTitle
     *
     * @param string $userTitle
     */
    public function setUserTitle($userTitle = null)
    {
        $this->userTitle = $userTitle;
    }

    /**
     * Get userTitle
     *
     * @return string 
     */
    public function getUserTitle()
    {
        return $this->userTitle;
    }

    public function getSlug()
    {
        return $this->slug;
    }
    
    /**
     * Set dateAdded
     *
     * @param datetime $dateAdded
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;
    }

    /**
     * Get dateAdded
     *
     * @return datetime 
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }
}