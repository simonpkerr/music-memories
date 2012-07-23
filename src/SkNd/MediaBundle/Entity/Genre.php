<?php

namespace SkNd\MediaBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use SkNd\MediaBundle\Entity\MediaType;

/**
 * SkNd\MediaBundle\Entity\Genre
 */
class Genre
{
    static public $default = 'all-genres';
    /**
     * @var integer $id
     */
    protected $id;

    //mapped to the media type
    protected $mediaType;
    
    protected $mediaType_id;
    
    /**
     * @var string $amazonBrowseNodeId
     */
    protected $amazonBrowseNodeId;

    /**
     * @var string $sevenDigitalTag
     */
    protected $sevenDigitalTag;

    /**
     * @var string $genreName
     */
    protected $genreName;
    
    /**
     * @var string $slug
     */
    protected $slug;
    
    /**
     * Get id
     *
     * @return integer 
     */
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

    /**
     * Set amazonBrowseNodeId
     *
     * @param string $amazonBrowseNodeId
     */
    public function setAmazonBrowseNodeId($amazonBrowseNodeId)
    {
        $this->amazonBrowseNodeId = $amazonBrowseNodeId;
    }

    /**
     * Get amazonBrowseNodeId
     *
     * @return string 
     */
    public function getAmazonBrowseNodeId()
    {
        return $this->amazonBrowseNodeId;
    }

    /**
     * Set sevenDigitalTag
     *
     * @param string $sevenDigitalTag
     */
    public function setSevenDigitalTag($sevenDigitalTag)
    {
        $this->sevenDigitalTag = $sevenDigitalTag;
    }

    /**
     * Get sevenDigitalTag
     *
     * @return string 
     */
    public function getSevenDigitalTag()
    {
        return $this->sevenDigitalTag;
    }
    
    /**
     * Set mediaType
     *
     * @param SkNd\MediaBundle\Entity\MediaType $mediaType
     */
    public function setMediaType(MediaType $mediaType)
    {
        $this->mediaType = $mediaType;
    }

    /**
     * Get mediaType
     *
     * @return SkNd\MediaBundle\Entity\MediaType $mediaType
     */
    public function getMediaType()
    {
        return $this->mediaType;
    }
   
    


    /**
     * Set genreName
     *
     * @param string $genreName
     */
    public function setGenreName($genreName)
    {
        $this->genreName = $genreName;
    }

    /**
     * Get genreName
     *
     * @return string 
     */
    public function getGenreName()
    {
        return $this->genreName;
    }
    
    /**
     * Set slug
     *
     * @param string $slug
     */
    /*public function setSlug($slug)
    {
        $this->slug = $slug;
    }*/

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }
}