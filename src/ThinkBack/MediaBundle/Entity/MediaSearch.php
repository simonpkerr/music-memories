<?php

namespace ThinkBack\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ThinkBack\MediaBundle\Entity\MediaSearch
 */
class MediaSearch
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $searchKeywords
     */
    private $searchKeywords;


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
     * Set searchKeywords
     *
     * @param string $searchKeywords
     */
    public function setSearchKeywords($searchKeywords)
    {
        $this->searchKeywords = $searchKeywords;
    }

    /**
     * Get searchKeywords
     *
     * @return string 
     */
    public function getSearchKeywords()
    {
        return $this->searchKeywords;
    }
}