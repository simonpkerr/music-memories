<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * Decade entity for getting and setting decade data
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

class Decade
{
    public static $default = 'all-decades';
    protected $id;
    protected $decadeName;
    protected $amazonBrowseNodeId;
    protected $sevenDigitalTag;
    protected $slug;
    public function getId()
    {
        return $this->id;
    }
   
    public function setDecadeName($decadeName)
    {
        $this->decadeName = $decadeName;
    }
    public function getDecadeName()
    {
        return $this->decadeName;
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
    public function getSlug()
    {
        return $this->slug;
    }
}