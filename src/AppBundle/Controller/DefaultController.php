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
     * @Route("/posts/new", name="new_post")
     */
    public function newPostAction(Request $request) 
    {
        // Only make the request submission on a POST request
        if($request->isMethod('POST')) {
            // Get the User's IP address
            $post_ip = self::getCurrentIp($this);
        
            // Need to get the current user based on security acces
            $user = self::getCurrentUser($this);
        
            // Get the body of the post from the request
            $body = $request->get('body');
        
            // We have everything we need now
            // Time to add the post to the database
            try {
                $em = self::getEntityManager();
                $post = new Post;
                $post->setBody($body);
                $post->setIpAddress($post_ip);
                $post->setUser($user);
                $em->persist($post);
                $em->flush();
                return new JsonResponse(array('status' => 200, 'message' => 'Success'));
            } catch (\Doctrine\DBAL\DBALException $e) {
                return new JsonResponse(array('status' => 400, 'message' => 'Unable to submit post.'));
            }   
        } else {
            return $this->render('default/new_post.html.twig');
        }
    }
    
    /**
     * @Route("/comments/new", name="new_comment")
     */
    public function newCommentAction(Request $request) 
    {
        // Only make the request submission on a POST request
        if($request->isMethod('POST')) {

            $post_id = $request->get('post_id');

            // Get the Post Number
            $post = $this->getDoctrine()
                     ->getRepository('AppBundle:Post')
                     ->find($post_id);

            // Need to get the current user based on security acces
            $user = self::getCurrentUser($this);

            // Get the User's IP address
            $comment_ip = self::getCurrentIp($this);
        
            // Get the body of the comment from the request
            $body = $request->get('body');
        
            // We have everything we need now
            // Time to add the post to the database
            try {
                $em = self::getEntityManager();
                $comment = new Comment;
                $comment->setPost($post);
                $comment->setBody($body);
                $comment->setIpAddress($comment_ip);
                $comment->setUser($user);
                $em->persist($comment);
                $em->flush();
                return new JsonResponse(array('status' => 200, 'message' => 'Success in posting comments'));
            } catch (\Doctrine\DBAL\DBALException $e) {
                return new JsonResponse(array('status' => 400, 'message' => 'Unable to comment.'));
            }   
        } else {
            return $this->render('post/post.html.twig');
        }
    }
    
    /**
     * @Route("/comments/new", name="new_comment")
     */
    public function newCommentAction(Request $request) 
    {
        // Only make the request submission on a POST request
        if($request->isMethod('POST')) {

            $post_id = $request->get('post_id');

            // Get the Post Number
            $post = $this->getDoctrine()
                     ->getRepository('AppBundle:Post')
                     ->find($post_id);

            // Need to get the current user based on security acces
            $user = self::getCurrentUser($this);

            // Get the User's IP address
            $comment_ip = self::getCurrentIp($this);
        
            // Get the body of the comment from the request
            $body = $request->get('body');
        
            // We have everything we need now
            // Time to add the post to the database
            try {
                $em = self::getEntityManager();
                $comment = new Comment;
                $comment->setPost($post);
                $comment->setBody($body);
                $comment->setIpAddress($comment_ip);
                $comment->setUser($user);
                $em->persist($comment);
                $em->flush();
                return new JsonResponse(array('status' => 200, 'message' => 'Success in posting comments'));
            } catch (\Doctrine\DBAL\DBALException $e) {
                return new JsonResponse(array('status' => 400, 'message' => 'Unable to comment.'));
            }   
        } else {
            return $this->render('post/post.html.twig');
        }
    }
    
    /**
     * @Route("/posts/new", name="new_post")
     */
    public function newPostAction(Request $request) 
    {
        // Only make the request submission on a POST request
        if($request->isMethod('POST')) {
            // Get the User's IP address
            $post_ip = self::getCurrentIp($this);
        
            // Need to get the current user based on security acces
            $user = self::getCurrentUser($this);
        
            // Get the body of the post from the request
            $body = $request->get('body');
        
            // We have everything we need now
            // Time to add the post to the database
            try {
                $em = self::getEntityManager();
                $post = new Post;
                $post->setBody($body);
                $post->setIpAddress($post_ip);
                $post->setUser($user);
                $post->setScore(self::hot(0, 0, new \DateTime()));
                $em->persist($post);
                $em->flush();
                return new JsonResponse(array('status' => 200, 'message' => 'Success'));
            } catch (\Doctrine\DBAL\DBALException $e) {
                return new JsonResponse(array('status' => 400, 'message' => 'Unable to submit post.'));
            }   
        } else {
            return $this->render('default/new_post.html.twig');
        }
    }

    /**
     * @Route("/posts/{post_id}", name="post_view")
     */
    public function viewPostAction(Request $request, $post_id) 
    {
        $user = self::getCurrentUser($this);
        
        // Get the post from the post_id in the database
        $post = $this->getDoctrine()
                     ->getRepository('AppBundle:Post')
                     ->find($post_id);
        
        $like = $this->getDoctrine()
                      ->getRepository('AppBundle:PostLikes')
                      ->findOneBy(array('post' => $post_id, 'user' => $user->getId()));
    
        // If anything other than a post is returned (including null)
        // throw an error.
        if (!$post) {
            throw $this->createNotFoundException(
                "Post not found!"
            );
        }
        
        return $this->render('default/post.html.twig', [
            'post' => $post, 
            'like' => $like
        ]);
    }
    
     /**
     * @Route("/upvote", name="upvote")
     * @Method({"POST"})
     */
    public function upvoteAction(Request $request) 
	{
        // Get post id from the request
        $post_id = $request->get("post_id");
        
        // Get current user
        $user = self::getCurrentUser($this);
        
        // Get the entity manager for Doctrine
        $em = self::getEntityManager();

		// Get the post from the post_id in the database
        $post = $em->getRepository('AppBundle:Post')
                   ->find($post_id);
    
        // If anything other than a post is returned (including null)
        // throw an error.
        if (!$post) {
            throw $this->createNotFoundException(
                'No post found for id ' . $id
            );
        }

        try{
            $like = $em->getRepository('AppBundle:PostLikes')
                       ->findOneBy(array('post' => $post_id, 'user' => $user->getId()));
            
            if(!isset($like)) {
                $like = new PostLikes;
                $like->setIsLike(true);
                $like->setUser(self::getCurrentUser($this));
                $like->setPost($post);
                $post->setUpvotes($post->getUpvotes() + 1);
                $post->addLike($like);
                $em->persist($like);
            } else {
                if($like->getIsLike()) {
                    $post->setUpvotes($post->getUpvotes() - 1);
                    $post->removeLike($like);
                    $em->remove($like);
                } else {
                    $post->setUpvotes($post->getUpvotes() + 1);
                    $post->setDownvotes($post->getDownvotes() - 1);
                    $like->setIsLike(true);
                    $em->persist($like);
                }
            }
            $post->setScore(self::hot($post->getUpvotes(), $post->getDownvotes(), $post->getCreated()));
            $em->persist($post);
            $em->flush();
            $score = ($post->getUpvotes() - $post->getDownvotes());
        } catch (\Docrine\DBAL\DBALException $e) {
            return new JsonResponse(array('status' => 400, 'message' => 'Unable to add like. $e->message'));
        }
        
        return new JsonResponse(array('status' => 200, 'message' => 'Success on upvote.', 'score' => $score));
	}
    
    /**
     * @Route("/downvote", name="downvote")
     * @Method({"POST"})
     */
    public function downvoteAction(Request $request) 
    {
        // Get the post_id
        $post_id = $request->get('post_id');
        
        // Get current user
        $user = self::getCurrentUser($this);
        
        // Get the entity manager
        $em = self::getEntityManager();
        
        // Get the post from the post_id in the database
        $post = $em->getRepository('AppBundle:Post')
                   ->find($post_id);
        
        // If anything other than a post is returned (including null)
        // throw an error.
        if (!$post) {
            throw $this->createNotFoundException(
                'No post found for id ' . $id
            );
        }

        // Try to add the downvote
        try {
            $like = $em->getRepository('AppBundle:PostLikes')
                       ->findOneBy(array('post' => $post_id, 'user' => $user->getId()));

            if(!isset($like)) {
                $dislike = new PostLikes;
                $dislike->setIsLike(false);
                $dislike->setUser(self::getCurrentUser($this));
                $dislike->setPost($post);
                $post->setDownvotes($post->getDownvotes() + 1);
                $post->addLike($dislike);
                $em->persist($dislike);
            } else {
                if($like->getIsLike()) {
                    $post->setUpvotes($post->getUpvotes() - 1);
                    $post->setDownvotes($post->getDownvotes() + 1);
                    $like->setIsLike(false);
                    $em->persist($like);
                } else {
                    $post->setDownvotes($post->getDownvotes() - 1);
                    $em->remove($like);
                    $post->removeLike($like);
                }
            }
            $post->setScore(self::hot($post->getUpvotes(), $post->getDownvotes(), $post->getCreated()));
            $em->persist($post);
            $em->flush();
            $score = ($post->getUpvotes() - $post->getDownvotes());
        } catch (\Doctrine\DBAL\DBALException $e) {
            return new JsonResponse(array('status' => 400, 'message' => 'Unable to dislike. $e->message'));  
        }

        return new JsonResponse(array('status' => 200, 'message' => 'Success on upvoting', 'score' => $score));
    }
    
    /**
     * @Route("/remove", name="remove")
     * @Method({"POST"})
     */
    public function removePost(Request $request) 
    {
         // Get post id from the request
        $post_id = $request->get("post_id");

        // Get the post from the post_id in the database
        $post = $this->getDoctrine()
                     ->getRepository('AppBundle:Post')
                     ->find($post_id);
    
        // If anything other than a post is returned (including null)
        // throw an error.
        if (!$post) {
            throw $this->createNotFoundException(
                'No post found for id ' . $id
            );
        }
        
        // Time to delete the post to the database
        try {
            $em = self::getEntityManager();
            $em->remove($post);
            $em->flush();
            return new JsonResponse(array('status' => 200, 'message' => 'Success'));
        } catch (\Doctrine\DBAL\DBALException $e) {
            return new JsonResponse(array('status' => 400, 'message' => 'Unable to delete post.'));
        }   
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
     * @Route("/confirm/{token}", name="confirm_email")
     */
    public function confirmEmailAction(Request $request, $token) {
        
        $em = self::getEntityManager();
        
        $auth = $em->getRepository("AppBundle:EmailAuth")
                   ->findOneBy(array('token' => $token, 'verified' => false));
        
        if(!$auth) {
            return $this->render(
                'security/confirm.html.twig',
                array(
                    'error' => 'Token is invalid or has already been used',
                )
            );
        }
            
        $user = $em->getRepository("AppBundle:User")
                   ->find($auth->getUser());
        
        if(!$user) {
            return $this->render(
                'security/confirm.html.twig',
                array(
                    'error' => 'User could not be found. Please contact support at adrestiaweb@gmail.com.',
                )
            );
        }
        
        $user->setEmailConfirmed(true);
        $auth->setVerified(true);
        $em->persist($user);
        $em->persist($auth);
        $em->flush();
        
        return $this->render(
            'security/confirm.html.twig'
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
