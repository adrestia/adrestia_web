<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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
}
