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
    protected $userManager;
    protected $currentUser;
    protected $session;
    protected $securityContext;
    
    public function __construct(EntityManager $em, UserManager $userManager, Session $session, SecurityContext $securityContext) {
        $this->em = $em;
        $this->userManager = $userManager;
        $this->session = $session;
        $this->securityContext = $securityContext;
        $this->currentUser = $this->getCurrentUser();
    }
    
    public function getOwnWall($id, $exceptionMessage = 'memoryWall.show.flash.accessDenied'){
        $mw = $this->getMemoryWall($id);
        if(!($this->memoryWallBelongsToUser($mw) || $this->securityContext->isGranted('ROLE_ADMIN'))){
            $this->session->getFlashBag()->add('notice', $exceptionMessage);
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        return $mw;
    }
    
    public function getUserManager(){
        return $this->userManager;
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
        if(count($mw) == 0)
            throw new NotFoundHttpException("Memory wall not found");
        
        //get all the memory wall content - TO DO TO REDUCE THE NUMBER OF DB CALLS
        $mw = array_pop($mw);
        /*$mwc = $this->em->getRepository('SkNdUserBundle:MemoryWallContent')->getMemoryWallContent($mw);
        
        if(!is_null($mwc)){
            $mw->setMemoryWallContent($mwc);
        }*/
        
        return $mw;
    }
    
    public function memoryWallBelongsToUser(MemoryWall $mw){
        //if the memory wall is private and the selected wall doesn't belong to the current user throw exception
        return ($this->currentUserIsAuthenticated() && $mw->getUser() == $this->getCurrentUser()) || $this->securityContext->isGranted('ROLE_ADMIN') == 1;        
        
    }
    
    public function getViewParams($scope){
        $pageTitle = "";
        
        if($scope == 'public'){
            $pageTitle = "All Public";
            //get all the public memory walls
            $mws = $this->em->getRepository('SkNdUserBundle:MemoryWall')->getPublicMemoryWalls();
        }else{
            if(is_object($this->currentUser) && ($this->currentUser->getUsernameCanonical() == $scope)){
                $pageTitle = 'My';
                //get all public and private walls for this user
                $mws = $this->currentUser->getMemoryWalls();
            }else{
                //get all public walls for the given user
                $user = $this->userManager->findUserByUsername($scope);
                if($user == null || !is_object($user) || !$user instanceof UserInterface)
                    throw new NotFoundHttpException("User not found");
                
                $pageTitle = $scope . htmlentities("'s");
                //call to getMemoryWalls with false indicates only get public walls
                $mws = $user->getMemoryWalls(false);
            }
        }
        
        $viewParams = array(
            'pageTitle'     => $pageTitle,
            'mws'           => $mws,
        );
        
        return $viewParams;
    }
    
}

?>
