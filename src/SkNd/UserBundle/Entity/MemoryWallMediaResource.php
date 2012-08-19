<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MemoryWallMediaResource gets and sets the named entity, managing the associations between media resources
 * and memory walls
 * @author Simon Kerr
 * @version 1.0
 */
namespace SkNd\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SkNd\UserBundle\Entity\MemoryWall;
use SkNd\MediaBundle\Entity\MediaResource;
use Doctrine\Common\Collections\ArrayCollection;

class MemoryWallMediaResource
{
    protected $api_id;
    protected $memoryWall;
    protected $mediaResource_id;
    protected $mediaResource;
    protected $userTitle;
    protected $slug;
    protected $dateAdded;
    
    public function getMemoryWallId()
    {
        return $this->memoryWall->getId();
    }
    public function getMediaResourceId()
    {
        return $this->mediaResource_id;
    }
    
    public function getId(){
        return array(
            $this->memoryWall->getId(),
            $this->mediaResource->getId(),
        );
    }
    
    public function getApi_id(){
        return $this->api_id;
    }
    
    public function __construct(MemoryWall $mw, MediaResource $mr){
        $this->memoryWall = $mw;
        $this->mediaResource = $mr;             
        $this->mediaResource_id = $mr->getId();
        $this->api_id = $mr->getAPI()->getId();
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

    public function setUserTitle($userTitle = null)
    {
        $this->userTitle = $userTitle;
    }

    public function getUserTitle()
    {
        return $this->userTitle;
    }

    public function getSlug()
    {
        return $this->slug;
    }
 
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;
    }

    public function getDateAdded()
    {
        return $this->dateAdded;
    }
}