<?php
/**
 * Description of MemoryWallAccessManager
 *
 * @author Simon Kerr
 * @copyright (c) 2013, Simon Kerr
 * @version 1.0
 */
namespace SkNd\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Model\UserManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use SkNd\UserBundle\Entity\MemoryWall;

class MemoryWallAccessManager {
    protected $em;
    protected $um;
    protected $currentUser;
    protected $session;
    protected $securityContext;
    
    public function __construct(EntityManager $em, UserManager $um, Session $session, SecurityContext $securityContext) {
        $this->em = $em;
        $this->um = $um;
        $this->session = $session;
        $this->securityContext = $securityContext;
    }
    
    public function getOwnWall($id, $exceptionMessage = 'memoryWall.show.flash.accessDenied'){
        $mw = $this->getMemoryWall($id);
        if(!$this->memoryWallBelongsToUser($mw)){
            $this->session->getFlashBag()->add('notice', $exceptionMessage);
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        return $mw;
    }
    
    public function getUserManager(){
        return $this->um;
    }
    
    public function currentUserIsAuthenticated(){
        $user = $this->getCurrentUser();
        return (is_object($user) && $user instanceof UserInterface);
    }
    
    public function getCurrentUser(){
        return $this->securityContext->getToken()->getUser();
    }
    
    public function getMemoryWall($id){
        $mw = $this->em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallById($id);
        //if memory wall doesn't exist
        if(is_null($mw))
            throw new NotFoundHttpException("Memory wall not found");
        
        return $mw;
    }
    
    public function memoryWallBelongsToUser(MemoryWall $mw){
        //if the memory wall is private and the selected wall doesn't belong to the current user throw exception
        return $this->currentUserIsAuthenticated() && $mw->getUser() == $this->getCurrentUser();        
        
    }
    
}

?>
