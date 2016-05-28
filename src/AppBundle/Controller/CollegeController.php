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
     * @Route("/{college_id}/posts/{post_id}", name="college_post_view", requirements={"college_id":"\d+", "post_id":"\d+"})
     */
    public function collegeViewPostAction(Request $request, $college_id, $post_id) 
    {
        $user = Utilities::getCurrentUser($this);
        $em = Utilities::getEntityManager($this);
        
        // Get the college
        $college = $em->getRepository('AppBundle:College')
                      ->find($college_id);
        
        // Get the post from the post_id in the database
        $post = $em->getRepository('AppBundle:Post')
                   ->findOneBy(array('id' => $post_id, 'hidden' => false));
        
        $like = $em->getRepository('AppBundle:PostLikes')
                   ->findOneBy(array('post' => $post_id, 'user' => $user->getId()));
        
        // If anything other than a post is returned (including null)
        // throw an error.
        if (!$post) {
            throw $this->createNotFoundException(
                "Post not found!"
            );
        }
        
        /*
        EQUIVALENT QUERY TO BUILDER BELOW

       "SELECT c.id, c.post_id, c.upvotes, 
                c.downvotes, c.body, c.reports, 
                p.created,
         FROM comments c
         WHERE c.post_id = :postid
         GROUP BY c.id, c.post_id, c.body, c.upvotes
                  c.downvotes, c.reports,
                  c.created
         ORDER BY created DESC;";
        
        */
        
        $builder = $em->createQueryBuilder();
        $builder
            ->select('c', 'l')
            ->from('AppBundle:Comment', 'c') 
            ->where('c.post = :postid AND c.hidden = false')
            ->setParameter('postid', $post->getId())
            ->leftJoin(
                'c.likes',
                'l',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'c.id = l.comment AND l.user = :user'
                )
            ->setParameter('user', $user->getId())
            ->groupBy('c', 'l')
            ->orderBy('c.created', 'ASC');
                
        $comments = $builder->getQuery()->getResult();
        
        return $this->render('posts/college_post.html.twig', [
            'post' => $post, 
            'comments' => $comments,
            'like' => $like,
            'college' => $college,
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
                p.downvotes, p.score,
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
