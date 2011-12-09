<?php

namespace ThinkBack\MediaBundle\Repository;

use Doctrine\ORM\EntityRepository;

class GenreRepository extends EntityRepository
{
    
    public function getAllGenres(){
        $genres = $this->createQueryBuilder('g')
                ->select(array('g.id', 'g.genreName', 'g.mediaType_id'))
                ->orderBy('g.genreName')
                ->getQuery()
                ->getResult();
        
        return json_encode($genres);  
    }
    
    public function getGenresByMediaType($mediaTypeId){
        $genres = $this->createQueryBuilder('g')
                ->select(array('g.id', 'g.genreName', 'g.mediaType_id'))
                ->where('g.mediaType_id == :mediaType')
                ->setParameter('mediaType', $mediaTypeId)
                ->getQuery()
                ->getResult();
        
        return $genres;
    }
    
    public function getSevenDigitalTagBySlug($slug){
        return $this->findOneBy(array('slug' => $slug));
    }
    
}