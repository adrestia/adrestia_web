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
use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use AppBundle\Entity\Post;
use AppBundle\Entity\PostLikes;
use AppBundle\Entity\Comment;

/**
 * @Route("/")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/{sorting}", name="homepage", defaults={"sorting":"hot"}, requirements={"sorting":"top|new|hot|^$"})
     * @Method({"GET"})
     */
    public function indexAction(Request $request, $sorting)
    {
        $em = self::getEntityManager();
        
        $user = self::getCurrentUser($this);
        
        /*
        EQUIVALENT QUERY TO BUILDER BELOW

       "SELECT p.id, p.user_id, p.body, p.upvotes, 
                p.downvotes, p.score, p.reports, 
                p.created, l.is_like, l.user_id, 
                l.post_id, SUM(p.upvotes - p.downvotes) AS top
         FROM posts p
         WHERE p.college = :college AND p.hidden = false
         LEFT JOIN post_likes l
         ON p.id = l.post_id AND l.user_id = ? 
         GROUP BY p.id, p.user_id, p.body, p.upvotes
                  p.downvotes, p.score, p.reports,
                  p.created, l.is_like, l.user_id,
                  l.post_id
         ORDER BY created DESC;";
        
        */
                 
        $builder = $em->createQueryBuilder();
        $builder
            ->select('p', 'l')
            ->addSelect('SUM(p.upvotes - p.downvotes) AS HIDDEN top')
            ->from('AppBundle:Post', 'p') 
            ->where('p.college = :college AND p.hidden = false')
            ->setParameter('college', $user->getCollege())
            ->leftJoin(
                'p.likes',
                'l',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'p.id = l.post AND l.user = :user'
                )
            ->setParameter('user', $user->getId())
            ->groupBy('p', 'l');
        
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
        
        return $this->render('default/index.html.twig', [
            'posts' => $posts,
            'sorting' => $sorting
        ]);
    }
    
    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'security/login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error'         => $error,
            )
        );
    }
    
    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginCheckAction(Request $request) {
        
    }
    
    /**
     * @Route("/terms", name="terms")
     */
    public function termsAction(Request $request) {
        return $this->render('default/terms.html.twig');
    }
    
    /**
     * @Route("/privacy", name="privacy")
     */
    public function privacyAction(Request $request) {
        return $this->render('default/privacy.html.twig');
    }
    
    /**
     * @Route("/content", name="content")
     */
    public function contentAction(Request $request) {
        return $this->render('default/content.html.twig');
    }
    
    /**
     * @return Doctrine entity manager
     */
    protected function getEntityManager() {
        return $this->get('doctrine')->getManager();
    }
    
    /**
     * @param $context – pss in $this as the variable
     * @return IP Address from the request
     */
    protected function getCurrentIp($context) {
        return $context->container->get('request_stack')->getMasterRequest()->getClientIp();
    }
    
    /**
     * @param $context – pass in $this as the variable
     * @return the User object that is currently authenticated
     */
    protected function getCurrentUser($context) {
        return $context->get('security.token_storage')->getToken()->getUser();
    }
    
    /**
     * The reddit hotness algorithm!
     *
     * @param $ups – Number of post upvotes
     * @param $downs – Number of post downvotes
     * @param $date – When the post was submitted
     *
     * @return calculated score of how hot a post is
     */
    private function hot($ups, $downs, $date) {
        $score = $ups - $downs;
        $order = log10(max(abs($score), 1));
        
        if($score > 0) {
            $sign = 1;
        } elseif($score < 0) {
            $sign = -1;
        } else {
            $sign = 0;
        }
        
        $seconds = $date->getTimestamp() - 1134028003;
        
        return round($order * $sign + $seconds / 45000, 7);
    }
}
