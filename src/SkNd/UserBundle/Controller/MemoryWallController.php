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
        
        $viewTitle = "";
        if($scope == 'public'){
            $viewTitle = "All public";
            //get all the public memory walls
        }else{
            $this->currentUser = $this->getCurrentUser();
            if($this->currentUser->getUsername() == $scope){
                $viewTitle = 'My';
            }else{
                //if request is for another user get the public walls for that user
                $user = $this->userManager->findUserByUsername($scope);
                if(!is_object($user) || !$user instanceof UserInterface)
                    throw new NotFoundHttpException("User not found");
                
                $viewTitle = $scope . '\'s';
            }
        }
        
        $viewParams = array(
            'pageTitle'     => $viewTitle,
            'pageContent'   => '',
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
        
        return $this->indexAction($this->currentUser->getUsername());
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
            if($form->isValid()){
                //persist data and redirect to new memory wall
                
            }            
        }
        return $this->render('SkNdUserBundle:MemoryWall:createMemoryWall.html.twig', array(
                   'form'   => $form->createView() 
        ));
    }
    
    private function currentUserIsAuthenticated($user){
        return (is_object($this->currentUser) && $this->currentUser instanceof UserInterface);
        
    }
    
    private function getCurrentUser(){
        return $this->container->get('security.context')->getToken()->getUser();
    }
}
