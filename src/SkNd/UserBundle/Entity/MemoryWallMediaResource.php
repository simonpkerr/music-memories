<?php

namespace SkNd\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SkNd\UserBundle\Entity\MemoryWall;
use SkNd\MediaBundle\Entity\MediaResource;

/**
 * SkNd\UserBundle\Entity\memoryWallMediaResource
 */
class memoryWallMediaResource
{

    protected $memoryWall;

    protected $mediaResource;

    protected $userTitle;

    protected $dateAdded;

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
    public function setUserTitle($userTitle)
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