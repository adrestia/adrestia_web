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
use AppBundle\Entity\Post;
use AppBundle\Entity\PostLikes;
use AppBundle\Entity\Comment;
use AppBundle\Entity\EmailAuth;
use AppBundle\Entity\PasswordAuth;
use AppBundle\Helper\Utilities;

/**
 * @Route("/admin")
 */
class AdminController extends Controller
{   
    /**
     * @Route("/", name="admin_home")
     */
    public function indexAction(Request $request)
    {   
        return $this->render('admin/index.html.twig', [
            
        ]);
    }
}
