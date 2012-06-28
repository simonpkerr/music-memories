<?php

namespace SkNd\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SkNd\MediaBundle\Entity\API
 */
class API
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var string $host
     */
    protected $host;
    
    protected $friendlyName;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
    
    public function setFriendlyName($name)
    {
        $this->friendlyName = $name;
    }

    public function getFriendlyName()
    {
        return $this->friendlyName;
    }

    
    /**
     * Set host
     *
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * Get host
     *
     * @return string 
     */
    public function getHost()
    {
        return $this->host;
    }
}