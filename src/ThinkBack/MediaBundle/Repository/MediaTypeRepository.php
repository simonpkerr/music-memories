<?php

namespace ThinkBack\MediaBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * MediaTypeRepository
 *
 */
class MediaTypeRepository extends EntityRepository
{
    public function getMediaTypes(){
        $mediaTypes = $this->findAll();            
                
        return $mediaTypes;                
    }
    
    public function getMediaTypeBySlug($slug){
        return $this->findOneBy(array('slug' => $slug));
    }
    
}