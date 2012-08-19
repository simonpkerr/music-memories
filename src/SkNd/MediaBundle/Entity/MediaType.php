<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MediaType gets and sets the current media type
 * checking for cached versions of details 
 * @author Simon Kerr
 * @version 1.0
 */


namespace SkNd\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use SkNd\MediaBundle\Entity\Genre;

class MediaType
{
    public static $default = "film-and-tv";
    protected $id;
    protected $mediaName;
    protected $amazonBrowseNodeId;
    protected $genres;
    protected $slug;
    
        
    public function __construct(){
        $this->genres = new ArrayCollection();
    }
    
    public function getGenres(){
        return $this->genres;
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function setMediaName($mediaName)
    {
        $this->mediaName = $mediaName;
    }

    public function getMediaName()
    {
        return $this->mediaName;
    }

    public function setAmazonBrowseNodeId($amazonBrowseNodeId)
    {
        $this->amazonBrowseNodeId = $amazonBrowseNodeId;
    }

    public function getAmazonBrowseNodeId()
    {
        return $this->amazonBrowseNodeId;
    }

    public function addGenre(Genre $genres)
    {
        $this->genres[] = $genres;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    public function getSlug()
    {
        return $this->slug;
    }
    

}