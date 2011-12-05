<?php

namespace ThinkBack\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use ThinkBack\MediaBundle\Controller\MediaController;

/**
 * ThinkBack\MediaBundle\Entity\MediaSearch
 */
class MediaSearch
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $searchKeywords
     */
    protected $searchKeywords;
    
    protected $searchSlug;


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
        $this->searchSlug = MediaController::slugify($searchKeywords);
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
    
    public function getSearchSlug(){
        return $this->searchSlug;
    }
}