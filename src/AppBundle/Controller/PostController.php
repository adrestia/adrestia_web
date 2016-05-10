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
class PostController extends Controller
{
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
                $post->setCollege($user->getCollege());
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
     * @Route("/posts/{post_id}", name="post_view", requirements={"post_id" = "\d+"})
     */
    public function viewPostAction(Request $request, $post_id) 
    {
        $user = self::getCurrentUser($this);
        
        $em = self::getEntityManager();
        
        // Get the post from the post_id in the database
        $post = $em->getRepository('AppBundle:Post')
                   ->find($post_id);
        
        $like = $em->getRepository('AppBundle:PostLikes')
                   ->findOneBy(array('post' => $post_id, 'user' => $user->getId()));
        
        /*
        EQUIVALENT QUERY TO BUILDER BELOW

       "SELECT c.id, c.post_id, c.upvotes, 
                c.downvotes, c.body, c.reports, 
                p.created, l.is_like, l.user_id, 
                l.comment_id
         FROM comments c
         WHERE c.post_id = :postid
         LEFT JOIN comment_likes l
         ON c.id = l.comment_id AND l.user_id = ? 
         GROUP BY c.id, c.post_id, c.body, c.upvotes
                  c.downvotes, c.reports,
                  c.created, l.is_like, l.user_id,
                  l.comment_id
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
            ->orderBy('c.created', 'DESC');
                
        $comments = $builder->getQuery()->getResult();
    
        // If anything other than a post is returned (including null)
        // throw an error.
        if (!$post) {
            throw $this->createNotFoundException(
                "Post not found!"
            );
        }
        
        return $this->render('default/post.html.twig', [
            'post' => $post, 
            'like' => $like,
            'comments' => $comments
        ]);
    }
    
     /**
     * @Route("/posts/upvote", name="upvote_post")
     * @Method({"POST"})
     */
    public function upvotePostAction(Request $request) 
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
     * @Route("/posts/downvote", name="downvote_post")
     * @Method({"POST"})
     */
    public function downvotePostAction(Request $request) 
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
     * @Route("/posts/remove", name="remove_post")
     * @Method({"DELETE"})
     */
    public function removePostAction(Request $request) 
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
        
        if($post->getUser() !== self::getCurrentUser($this)) {
            return new JsonResponse(
                array(
                    'status' => 403, 
                    'message' => strtr("That is not your post. -_-"),
                    )
            );
        }
        
        // Time to delete the post to the database
        try {
            $em = self::getEntityManager();
            $post->setHidden(true);
            $em->persist($post);
            $em->flush();
            return new JsonResponse(array('status' => 200, 'message' => 'Success'));
        } catch (\Doctrine\DBAL\DBALException $e) {
            return new JsonResponse(array('status' => 400, 'message' => 'Unable to delete post.'));
        }   
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
}