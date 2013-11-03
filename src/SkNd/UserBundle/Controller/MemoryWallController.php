<?php

namespace SkNd\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use FOS\UserBundle\Model\UserInterface;
use SkNd\UserBundle\Entity\MemoryWall;
use SkNd\UserBundle\Form\Type\MemoryWallType;
use SkNd\MediaBundle\MediaAPI\ProcessBatchStrategy;

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MemoryWallController controls also aspects of showing, editing, deleting memory walls 
 * @author Simon Kerr
 * @version 2.0
 */

class MemoryWallController extends Controller
{
    protected $currentUser;
    protected $em;
    protected $userManager;
    protected $mwAccessManager;
    
    private function getMWAccessManager(){
        return $this->container->get('sk_nd_user.mw_access_manager');
    }
    
    private function getEntityManager(){
        return $this->container->get('sk_nd_media.mediaapi')->getEntityManager();
    }
        
    /**
     * @method indexAction is used to show all public memory walls,
     * public memory walls for a given user, or all private and public 
     * memory walls for an authenticated user
     * @param $scope can either be 'all-public' to show all public walls, or a username to show a specific users walls
     * @return type view
     */
    public function indexAction($scope = 'public')
    {
        $this->mwAccessManager = $this->getMWAccessManager();
        $viewParams = $this->mwAccessManager->getViewParams($scope);
        return $this->render('SkNdUserBundle:MemoryWall:index.html.twig', $viewParams);
    }
    
    
    
    public function personalIndexAction()
    {
        $this->mwAccessManager = $this->getMWAccessManager();
        $this->em = $this->getEntityManager();
        $this->currentUser = $this->mwAccessManager->getCurrentUser();
        if(!$this->mwAccessManager->currentUserIsAuthenticated()){
            $this->get('session')->getFlashBag()->add('notice', 'memoryWall.showOwnWalls.flash.accessDenied');
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        return $this->indexAction($this->currentUser->getUsernameCanonical());
    }
    
    public function createAction(Request $request = null){
        $this->mwAccessManager = $this->getMWAccessManager();
        $this->em = $this->getEntityManager();
        $session = $this->get('session');
        $this->userManager = $this->mwAccessManager->getUserManager();
        $this->currentUser = $this->mwAccessManager->getCurrentUser();
        
        if(!$this->mwAccessManager->currentUserIsAuthenticated()){
            $session->getFlashBag()->add('notice', 'memoryWall.create.flash.accessDenied');
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        $mw = new MemoryWall($this->currentUser);
        $form = $this->createForm(new MemoryWallType(), $mw); 
        if($request->getMethod() == 'POST'){
            $form->bind($request);
            //check form is valid 
            if($form->isValid()){
                $mw = $form->getData();
                $this->currentUser->addMemoryWall($mw);
                $this->userManager->updateUser($this->currentUser);
                
                //persist data and redirect to new memory wall
                $session->getFlashBag()->add('notice', 'memoryWall.create.flash.success');
                return $this->redirect($this->generateUrl('memoryWallShow', array(
                    'id'    => $mw->getId(),
                    'slug'  => $mw->getSlug(),
                    )
                ));
            }            
        }
        return $this->render('SkNdUserBundle:MemoryWall:createMemoryWall.html.twig', array(
            'form'   => $form->createView() 
        ));
    }
    
    public function showAction($id, $slug, $page = 1){
        $this->mwAccessManager = $this->getMWAccessManager();
        $this->em = $this->getEntityManager();
        $session = $this->get('session');
        $mediaapi = $this->get('sk_nd_media.mediaapi');
        
        $mw = $this->mwAccessManager->getMemoryWall($id);
        $wallBelongsToCurrentUser = $this->mwAccessManager->memoryWallBelongsToUser($mw);
        //private walls
        if(!$mw->getIsPublic() && !$wallBelongsToCurrentUser){
            $session->getFlashBag()->add('notice', 'memoryWall.show.flash.accessDenied');
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        //check the mediaresources related to this wall and refresh from api if necessary
        $mediaResources = $mw->getMediaResources(); 
        if(count($mediaResources) > 0){
            $processStrategy = new ProcessBatchStrategy(array(
                'em'                => $this->em,
                'apis'              => $mediaapi->getAPIs(),
                'mediaResources'    => $mediaResources,
            ));
            $mediaapi->getMedia($processStrategy);
        }
        
        $returnUrl = $this->getRequest()->headers->get('referer');
        if(strpos($returnUrl, 'details') === false && strpos($returnUrl, 'search') === false){
            $returnUrl = null;
        }
                
        return $this->render('SkNdUserBundle:MemoryWall:showMemoryWall.html.twig', array (
            'mw'                        => $mw,
            'wallBelongsToCurrentUser'  => $wallBelongsToCurrentUser,
            'apis'                      => $this->em->getRepository('SkNdMediaBundle:API')->findAll(),
            'returnUrl'                 => $returnUrl,
        ));
    }
    
    public function editAction($id, $slug, Request $request = null){
        $this->mwAccessManager = $this->getMWAccessManager();
        $this->em = $this->getEntityManager();
        $session = $this->get('session');
        $mw = $this->mwAccessManager->getOwnWall($id, 'memoryWall.edit.flash.accessDenied');
        
        $form = $this->createForm(new MemoryWallType(), $mw); 
        $form->handleRequest($request);
                    
        //check form is valid 
        if($form->isValid()){
            $mw = $form->getData();
            $this->em->persist($mw);
            $this->em->flush();
            $session->getFlashBag()->add('notice', 'memoryWall.edit.flash.success');
            return $this->redirect($this->generateUrl('memoryWallShow', array(
                'id'    => $mw->getId(),
                'slug'  => $mw->getSlug(),
                )
            ));
        }    
                
        return $this->render('SkNdUserBundle:MemoryWall:editMemoryWall.html.twig', array(
            'form'          => $form->createView(),
            'mw'            => $mw,
        ));
    }
    
    public function deleteAction($id, $slug){
        $this->mwAccessManager = $this->getMWAccessManager();
        $this->em = $this->getEntityManager();
        $mw = $this->mwAccessManager->getOwnWall($id, 'memoryWall.delete.flash.accessDenied');
        $this->get('session')->set('tokens/SkNd-delete-token', true);
        return $this->render('SkNdUserBundle:MemoryWall:deleteMemoryWall.html.twig', array(
            'mw'    =>  $mw,
        ));
    }
    
    public function deleteConfirmAction($id, $slug){
        $this->mwAccessManager = $this->getMWAccessManager();
        $this->em = $this->getEntityManager();        
        $session = $this->get('session');
        $this->currentUser = $this->mwAccessManager->getCurrentUser();
        
        //if token wasn't set in delete action, throw exception
        if(!$session->get('tokens/SkNd-delete-token'))
            throw $this->createNotFoundException("Memory wall cannot be deleted");
        else{
            $session->remove('tokens/SkNd-delete-token');
        }
        $mw = $this->mwAccessManager->getOwnWall($id);
        $this->em->remove($mw);
        
        //if this was the last wall, delete but create a new default one
        if($this->currentUser->getMemoryWalls()->count() == 1){
            $session->getFlashBag()->add('notice', 'memoryWall.delete.flash.successCreatedDefault');
            $this->currentUser->createDefaultMemoryWall();
        }else{
            $session->getFlashBag()->add('notice', 'memoryWall.delete.flash.success');
        }
        
        $this->em->flush();
                
        return $this->redirect($this->generateUrl('memoryWallsPersonalIndex'));
    }
}
