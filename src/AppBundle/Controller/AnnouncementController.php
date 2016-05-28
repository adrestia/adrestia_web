<?php

namespace AppBundle\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query\ResultSetMapping;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form\ChangePasswordType;
use AppBundle\Form\ResetPasswordType;
use AppBundle\Form\Model\ChangePassword;
use AppBundle\Form\Model\ResetPassword;
use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use AppBundle\Entity\Announcement;
use AppBundle\Helper\Utilities;

/**
 * @Route("/announcements")
 */
class AnnouncementController extends Controller
{   
    /**
     * @Route("/", name="announcement_home")
     */
    public function indexAction(Request $request)
    {   
        $em = Utilities::getEntityManager($this);
        
        $announcements = $em->getRepository('AppBundle:Announcement')
                            ->findAll();
        
        return $this->render('announcements/index.html.twig', [
            'announcements' => $announcements,
        ]);
    }
    
    /**
     * @Route("/{announcement_id}", name="announcement_home", requirements={"announcement_id" = "\d+"})
     */
    public function announcementViewAction(Request $request, $announcement_id)
    {   
        $em = Utilities::getEntityManager($this);
        
        $announcement = $em->getRepository('AppBundle:Announcement')
                            ->find($announcement_id);
        
        return $this->render('announcements/announcement.html.twig', [
            'announcement' => $announcement,
        ]);
    }
    
    /**
     * @Route("/new", name="new_announcement")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAnnouncementAction(Request $request)
    {   
        // Only make the request submission on a POST request
        if($request->isMethod('POST')) {
        
            // Get the body of the post from the request
            $body = $request->get('body');
            
            if(trim($body) === '') {
                return new JsonResponse(array('status' => 400, 'message' => "Empty body"));
            }
            
            $body = preg_replace("/[\r\n]{2,}/", "\n\n", $body); 
        
            // We have everything we need now
            // Time to add the post to the database
            try {
                $em = Utilities::getEntityManager($this);
                $announcement = new Announcement;
                $announcement->setBody($body);
                $em->persist($announcement);
                $em->flush();
                return new JsonResponse(array('message' => 'Success', 'announcement_id' => $announcement->getId()), 200);
            } catch (\Doctrine\DBAL\DBALException $e) {
                return new JsonResponse(array('status' => 400, 'message' => 'Unable to submit announcement.'));
            }   
        } else {
            return $this->render('admin/new_announcement.html.twig');
        }
    }
}
