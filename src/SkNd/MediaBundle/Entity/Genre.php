<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * Genre entity for getting and setting the genre
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use SkNd\MediaBundle\Entity\MediaType;

class Genre
{
    static public $default = 'all-genres';
    protected $id;
    protected $mediaType;
    protected $mediaType_id;
    protected $amazonBrowseNodeId;
    protected $sevenDigitalTag;
    protected $genreName;
    protected $slug;
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setMediaTypeId($mediaTypeId)
    {
        $this->mediaType_id = $mediaTypeId;
    }
    
    public function getMediaTypeId()
    {
        return $this->mediaType_id;
    }

    public function setAmazonBrowseNodeId($amazonBrowseNodeId)
    {
        $this->amazonBrowseNodeId = $amazonBrowseNodeId;
    }

    public function getAmazonBrowseNodeId()
    {
        return $this->amazonBrowseNodeId;
    }

    public function setSevenDigitalTag($sevenDigitalTag)
    {
        $this->sevenDigitalTag = $sevenDigitalTag;
    }

    public function getSevenDigitalTag()
    {
        return $this->sevenDigitalTag;
    }
    
    public function setMediaType(MediaType $mediaType)
    {
        $this->mediaType = $mediaType;
    }
    public function getMediaType()
    {
        return $this->mediaType;
    }
   
    public function setGenreName($genreName)
    {
        $this->genreName = $genreName;
    }

    public function getGenreName()
    {
        return $this->genreName;
    }
    
    public function getSlug()
    {
        return $this->slug;
    }
}