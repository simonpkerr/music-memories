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
     * @var string $apiName
     */
    protected $apiName;

    /**
     * @var string $host
     */
    protected $host;


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
     * Set apiName
     *
     * @param string $apiName
     */
    public function setApiName($apiName)
    {
        $this->apiName = $apiName;
    }

    /**
     * Get apiName
     *
     * @return string 
     */
    public function getApiName()
    {
        return $this->apiName;
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