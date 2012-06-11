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
                if(!is_object($user) || !$user instanceof UserInterface)
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
        
        return $this->render('SkNdUserBundle:MemoryWall:index.html.twig', $viewParams);
    }
    
    public function personalIndexAction()
    {
        $this->currentUser = $this->getCurrentUser();
        if(!$this->currentUserIsAuthenticated($this->currentUser)){
            $this->get('session')->setFlash('notice', 'Naughty naughty. You have to log in first to see your own Memory Walls');
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        return $this->indexAction($this->currentUser->getUsernameCanonical());
    }
    
    public function createAction(Request $request = null){
        $this->currentUser = $this->getCurrentUser();
        if(!$this->currentUserIsAuthenticated($this->currentUser)){
            $this->get('session')->setFlash('notice', 'A new Memory Wall eh? Nice. Just log in first and then you\'re away!');
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        $mw = new MemoryWall();
        $form = $this->createForm(new MemoryWallType(), $mw); 
        if($request->getMethod() == 'POST'){
            $form->bindRequest($request);
            //check form is valid and ensure this user hasn't created an identically named wall
            if($form->isValid()){
                $mw = $form->getData();
                //persist data and redirect to new memory wall
                $this->get('session')->setFlash('notice', 'Success! Now, get started adding loads of cool stuff to your new Memory Wall');
                $this->em->persist($mw);
                $this->em->flush();
                return $this->redirect($this->generateUrl('memoryWallShow', $mw->getSlug()));
            }            
        }
        return $this->render('SkNdUserBundle:MemoryWall:createMemoryWall.html.twig', array(
            'form'   => $form->createView() 
        ));
    }
    
    public function showAction($slug){
        $this->em = $this->getEntityManager();
        $mw = $this->em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallById($id);
        
        return $this->render('SkNdUserBundle:MemoryWall:showMemoryWall.html.twig', array (
            'memoryWall'    =>  $mw,
        ));
    }
    
    private function currentUserIsAuthenticated($user){
        return (is_object($this->currentUser) && $this->currentUser instanceof UserInterface);
        
    }
    
    private function getCurrentUser(){
        return $this->container->get('security.context')->getToken()->getUser();
    }
}
