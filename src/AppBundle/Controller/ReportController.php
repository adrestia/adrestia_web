<?php

namespace AppBundle\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query\ResultSetMapping;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use AppBundle\Entity\User;
use AppBundle\Entity\Post;
use AppBundle\Helper\Utilities;

/**
 * @Route("/report")
 */
class ReportController extends Controller
{
    /**
     * @Route("/post", name="report_post")
     * @Method({"POST"})
     *
     * @param post_id 
     * @param reason_id
     *
     */
    public function reportPostAction(Request $request) 
    {   
        // Get current user and entity manager
        $user   = Utilities::getCurrentUser($this);
        $em     = Utilities::getEntityManager($this);
        
        // Get the post
        $post_id = $request->get('post_id');
        $post = $em->getRepository('AppBundle:Post')
                   ->find($post_id);
        
        // Get the reason for the report
        $reason_id  = $request->get('reason_id');
        $reason     = $em->getRepository('AppBundle:ReportReason')
                         ->find($reason_id);
        
        // Check if already reported
        $report = $em->getRepository('AppBundle:Report')
                     ->findOneBy(array('user' => $user, 'post' => $post));
        
        if(!$report || !empty($report)) {
            return new JsonResponse(array('message' => 'Report has already been submitted for this post'), 403);
        }
    
        // We have everything we need now
        // Time to add the post to the database
        try {
            $em = Utilities::getEntityManager($this);
            $report = new Report;
            $report->setReason($reason);
            $report->setPost($post);
            $report->setUser($user);
            $em->persist($report);
            $em->flush();
            return new JsonResponse(array('message' => 'Success'), 200);
        } catch (\Doctrine\DBAL\DBALException $e) {
            return new JsonResponse(array('message' => 'Unable to submit report'), 400);
        }   
    }
}