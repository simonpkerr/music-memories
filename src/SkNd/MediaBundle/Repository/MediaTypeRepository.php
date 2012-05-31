<?php

namespace SkNd\MediaBundle\Repository;

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
    
    public function getDefaultMediaType(){
        return $this->findOneBy(array('slug' => 'film-and-tv'));
    }
    
}