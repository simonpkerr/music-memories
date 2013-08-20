<?php
/**
 * MemoryWallContentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */

namespace SkNd\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SkNd\UserBundle\Entity\MemoryWall;
use Doctrine\Common\Collections\ArrayCollection;

class MemoryWallContentRepository extends EntityRepository
{
    
    public function getMemoryWallContent(MemoryWall $mw){
        //get all memory wall content then filter and return based on discr
        /*$mwmrs = $this->createQueryBuilder('mwc')
                ->where('mwc.memoryWall = :mw')
                ->andWhere('mwc.disc = :disc')
                //->innerJoin('u.Phonenumbers', 'p', Expr\Join::WITH, 'p.is_primary = 1')
                ->addOrderBy('mwc.lastModified', 'DESC')
                ->setParameter('mw', $mw)
                ->setParameter('disc', 'memorywallmediaresource')
                ->getQuery()
                ->getResult();*/
        
        /*$ugc = $this->createQueryBuilder('mwc')
                ->where('mwc.memoryWall = :mw')
                ->andWhere('mwc.disc = :disc')
                ->addOrderBy('mwc.lastModified', 'DESC')
                ->setParameter('mw', $mw)
                ->setParameter('disc', 'ugc')
                ->getQuery()
                ->getResult();*/
        
        /*return array(
            'mwmrs' => $mwmrs,
            'ugc'   => $ugc,
        )*/
        
        $q = $this->createQueryBuilder('mwc')
                ->where('mwc.memoryWall = :mw')
                ->addOrderBy('mwc.lastModified', 'DESC')
                ->setParameter('mw', $mw)
                ->innerJoin('mwc.mediaResource', 'mr')
                ->innerJoin('mr.mediaResourceCache', 'mrc')
                ->getQuery();
        
        return new ArrayCollection($q->getResult(\Doctrine\ORM\Query::HYDRATE_OBJECT));
        
       
    }
    
}
