<?php

namespace ThinkBack\MediaBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * ThinkBack\MediaBundle\Entity\Decade
 */
class Decade
{
    /**
     * @var integer $id
     */
    private $id;

    //associatied media type
    //protected $mediaType;
    //private $mediaType_id;
    
    /**
     * @var integer $decadeName
     */
    private $decadeName;

    /**
     * @var string $amazonBrowseNodeId
     */
    private $amazonBrowseNodeId;

    /**
     * @var string $sevenDigitalTag
     */
    private $sevenDigitalTag;


    /**
     * @var string $slug
     */
    private $slug;

    
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
     * Set decadeName
     *
     * @param integer $decadeName
     */
    public function setDecadeName($decadeName)
    {
        $this->decadeName = $decadeName;
    }

    /**
     * Get decadeName
     *
     * @return integer 
     */
    public function getDecadeName()
    {
        return $this->decadeName;
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
}