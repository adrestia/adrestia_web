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
 * @Route("/colleges")
 */
class CollegeController extends Controller
{   
    /**
     * @Route("/", name="college_home")
     */
    public function collegePickAction(Request $request)
    {
        $em = Utilities::getEntityManager($this);
        
        $user = Utilities::getCurrentUser($this);
        
        /*
        EQUIVALENT QUERY TO BUILDER BELOW

       "SELECT p.id, p.user_id, p.body, p.upvotes, 
                p.downvotes, p.score, p.reports, 
                p.created, (p.upvotes - p.downvotes) AS top
         FROM posts p
         WHERE p.hidden = false
         GROUP BY p.id, p.user_id, p.body, p.upvotes
                  p.downvotes, p.score, p.reports,
                  p.created
         ORDER BY hot DESC
         LIMIT 25;"
        */
                 
        $builder = $em->createQueryBuilder();
        $builder
            ->select('p')
            ->addSelect('(p.upvotes - p.downvotes) AS HIDDEN top')
            ->from('AppBundle:Post', 'p') 
            ->where('p.hidden = false')
            ->groupBy('p')
            ->setMaxResults(25)
            ->orderBy('p.score', 'DESC');
        
        $posts = $builder->getQuery()->getResult();
        
        
        // Simply selecting all colleges
        $builder = $em->createQueryBuilder()->select('c')->from('AppBundle:College', 'c');
        $colleges = $builder->getQuery()->getResult();
                
        return $this->render('posts/college_view.html.twig', [
            'posts' => $posts,
            'colleges' => $colleges,
        ]);
    }

    /**
     * @Route("/{college_id}/{sorting}", name="college_page", defaults={"sorting":"hot"}, requirements={"sorting":"top|new|hot|^$", "college_id" : "\d+"})
     */
    public function collegePageAction(Request $request, $college_id, $sorting)
    {
        $em = Utilities::getEntityManager($this);
        $user = Utilities::getCurrentUser($this);
        
        /*
        EQUIVALENT QUERY TO BUILDER BELOW

       "SELECT p.id, p.user_id, p.body, p.upvotes, 
                p.downvotes, p.score, p.reports, 
                p.created, (p.upvotes - p.downvotes) AS top
         FROM posts p
         WHERE p.college = :college AND p.hidden = false
         GROUP BY p.id, p.user_id, p.body, p.upvotes
                  p.downvotes, p.score, p.reports,
                  p.created
         ORDER BY created DESC;";
        */
                 
        $builder = $em->createQueryBuilder();
        $builder
            ->select('p')
            ->addSelect('(p.upvotes - p.downvotes) AS HIDDEN top')
            ->from('AppBundle:Post', 'p') 
            ->where('p.college = :college AND p.hidden = false')
            ->setParameter('college', $college_id)
            ->groupBy('p');
        
        $college = $em->getRepository('AppBundle:College')
                      ->find($college_id);
        
        $sorting = strtolower($sorting);
        if($sorting === "new") {
            $builder->orderBy('p.created', 'DESC');
        } elseif ($sorting === "top") {
            $builder->orderBy('top', 'DESC');
        } elseif ($sorting === "hot") {
            $builder->orderBy('p.score', 'DESC');
        } else {
            $sorting === "hot";
            $builder->orderBy('p.created', 'DESC');
        }
                
        $posts = $builder->getQuery()->getResult();
        
        return $this->render('posts/college.html.twig', [
            'posts' => $posts,
            'sorting' => $sorting,
            'college' => $college,
        ]);
    }
}
