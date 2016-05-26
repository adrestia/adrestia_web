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
 * @Route("/")
 */
class ReportController extends Controller
{
    /**
     * @Route("/posts/report", name="report_post")
     * @Method({"POST"})
     */
    public function newPostAction(Request $request) 
    {   
        // Need to get the current user based on security acces
        $user = Utilities::getCurrentUser($this);
    
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
            $post = new Post;
            $post->setBody($body);
            $post->setScore(Utilities::hot(0, 0, new \DateTime('NOW')));
            $post->setIpAddress($post_ip);
            $post->setCollege($user->getCollege());
            $post->setUser($user);
            $em->persist($post);
            $em->flush();
            return new JsonResponse(array('status' => 200, 'message' => 'Success', 'post_id' => $post->getId()));
        } catch (\Doctrine\DBAL\DBALException $e) {
            return new JsonResponse(array('status' => 400, 'message' => 'Unable to submit post.'));
        }   
    }
}