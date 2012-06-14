<?php

namespace SkNd\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Component\HttpKernel\Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use FOS\UserBundle\Model\UserInterface;
use SkNd\UserBundle\Entity\MemoryWall;
use SkNd\UserBundle\Form\Type\MemoryWallType;


/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MemoryWallController controls also aspects of showing, editing, deleting memory walls and their content
 * @author Simon Kerr
 * @version 1.0
 */

class MemoryWallController extends Controller
{
    protected $currentUser;
    protected $em;
    protected $userManager;
    
    private function getEntityManager(){
        return $this->getDoctrine()->getEntityManager();
    }
        
    protected function getUserManager(){
        return $this->container->get('fos_user.user_manager');
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
        $this->userManager = $this->getUserManager();
        $this->em = $this->getEntityManager();
        
        $pageTitle = "";
        
        if($scope == 'public'){
            $pageTitle = "All Public";
            //get all the public memory walls
            $mws = $this->em->getRepository('SkNdUserBundle:MemoryWall')->getPublicMemoryWalls();
        }else{
            $this->currentUser = $this->getCurrentUser();
            if(is_object($this->currentUser) && ($this->currentUser->getUsername() == $scope)){
                $pageTitle = 'My';
                //get all public and private walls for this user
                $mws = $this->currentUser->getMemoryWalls();
            }else{
                //get all public walls for the given user
                $user = $this->userManager->findUserByUsername($scope);
                if($user == null || !is_object($user) || !$user instanceof UserInterface)
                    throw $this->createNotFoundException("User not found");
                
                $pageTitle = $scope . htmlentities("'s");
                //call to getMemoryWalls with false indicates only get public walls
                $mws = $user->getMemoryWalls(false);
            }
        }
        
        $viewParams = array(
            'pageTitle'     => $pageTitle,
            'mws'           => $mws,
        );
        
        return $this->render('SkNdUserBundle:MemoryWall:index.html.twig', $viewParams);
    }
    
    public function personalIndexAction()
    {
        $this->currentUser = $this->getCurrentUser();
        if(!$this->currentUserIsAuthenticated($this->currentUser)){
            $this->get('session')->setFlash('notice', 'memoryWall.showOwnWalls.flash.accessDenied');
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        return $this->indexAction($this->currentUser->getUsernameCanonical());
    }
    
    public function createAction(Request $request = null){
        $this->userManager = $this->getUserManager();
        $this->currentUser = $this->getCurrentUser();
        if(!$this->currentUserIsAuthenticated($this->currentUser)){
            $this->get('session')->setFlash('notice', 'memoryWall.create.flash.accessDenied');
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        $mw = new MemoryWall();
        $form = $this->createForm(new MemoryWallType(), $mw); 
        if($request->getMethod() == 'POST'){
            $form->bindRequest($request);
            //check form is valid 
            if($form->isValid()){
                $mw = $form->getData();
                $this->currentUser->addMemoryWall($mw);
                $this->userManager->updateUser($this->currentUser);
                
                //persist data and redirect to new memory wall
                $this->get('session')->setFlash('notice', 'memoryWall.create.flash.success');
                return $this->redirect($this->generateUrl('memoryWallShow', array('slug' => $mw->getSlug())));
            }            
        }
        return $this->render('SkNdUserBundle:MemoryWall:createMemoryWall.html.twig', array(
            'form'   => $form->createView() 
        ));
    }
    
    public function showAction($slug){
        $mw = $this->getMemoryWall($slug);
        $wallBelongsToCurrentUser = $this->memoryWallBelongsToUser($mw);
        if(!$mw->getIsPublic() && !$wallBelongsToCurrentUser){
            $this->get('session')->setFlash('notice', 'memoryWall.show.flash.accessDenied');
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        return $this->render('SkNdUserBundle:MemoryWall:showMemoryWall.html.twig', array (
            'mw'                        => $mw,
            'wallBelongsToCurrentUser'  => $wallBelongsToCurrentUser,
        ));
    }
    
    public function editAction($slug, Request $request = null){
        $this->em = $this->getEntityManager();
        $mw = $this->getMemoryWall($slug);
        $form = $this->createForm(new MemoryWallType(), $mw); 
        
        //if the wall belongs to this user allow edit
        if(!$this->memoryWallBelongsToUser($mw)){
            $this->get('session')->setFlash('notice', 'memoryWall.edit.flash.accessDenied');
            throw new AccessDeniedException('This user does not have access to this section.');
        }else{
            $this->get('session')->setFlash('notice', '');
        }
        
        if("POST" === $request->getMethod()){
            $form->bindRequest($request);
            //check form is valid 
            if($form->isValid()){
                $mw = $form->getData();
                $this->em->persist($mw);
                $this->em->flush();
                $this->get('session')->setFlash('notice', 'memoryWall.edit.flash.success');
                return $this->redirect($this->generateUrl('memoryWallShow', array('slug' => $mw->getSlug())));
            }    
        }
        
        return $this->render('SkNdUserBundle:MemoryWall:editMemoryWall.html.twig', array(
            'form'          => $form->createView(),
            'slug'          => $mw->getSlug(),
        ));
       
    }
    
    public function deleteAction($slug){
        $mw = $this->getOwnWall($slug);
        
        return $this->render('SkNdUserBundle:MemoryWall:deleteMemoryWall.html.twig', array(
            'mw'    =>  $mw,
        ));
       
    }
    
    public function deleteConfirmAction($slug){
        $mw = $this->getOwnWall($slug);
        $this->em->remove($mw);
        $this->em->flush();
        
        $this->get('session')->setFlash('notice', 'memoryWall.delete.flash.success');
        return $this->redirect($this->generateUrl('memoryWallsPersonalIndex'));
    }
    
    
    public function addMediaResourceAction($id){
        
    }
    
    private function getOwnWall($slug){
        $mw = $this->getMemoryWall($slug);
        if(!$this->memoryWallBelongsToUser($mw)){
            $this->get('session')->setFlash('notice', 'memoryWall.delete.flash.accessDenied');
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        return $mw;
    }
    
    private function currentUserIsAuthenticated($user){
        return (is_object($user) && $user instanceof UserInterface);
    }
    
    private function getCurrentUser(){
        return $this->container->get('security.context')->getToken()->getUser();
    }
    
    private function getMemoryWall($slug){
        $this->em = $this->getEntityManager();
        $mw = $this->em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug($slug);
        //if memory wall doesn't exist
        if(is_null($mw))
            throw $this->createNotFoundException("Memory wall not found");
        
        return $mw;
    }
    
    private function memoryWallBelongsToUser(MemoryWall $mw){
        $this->currentUser = $this->getCurrentUser();
        //if the memory wall is private and the selected wall doesn't belong to the current user throw exception
        return $this->currentUserIsAuthenticated($this->currentUser) && $mw->getUser() == $this->currentUser;        
        
    }
}
