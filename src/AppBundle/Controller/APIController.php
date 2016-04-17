<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class APIController extends Controller
{
    /**
     * @Route("/", name="api_home")
     */
    public function indexAction(Request $request)
    {
        return new JsonResponse(array('name' => $name));
    }
    
    /**
     * @Route("/users/{id}", name="get_user", requirements={"id" = "\d+"})
     */ 
    public function getUserAction(Request $request, $id)
    {
        $user = $this->getDoctrine()
                ->getRepository('AppBundle:User')
                ->find($id);
    
        if (!$user) {
            throw $this->createNotFoundException(
                'No product found for id ' . $id
            );
        }
        
        $serializer = $this->container->get('serializer');
        $reports = $serializer->serialize($user, 'json');
        return new Response($reports);
    }
}
