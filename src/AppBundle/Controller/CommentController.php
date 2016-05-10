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
use AppBundle\Helper\Utilities;

/**
 * @Route("/")
 */
class CommentController extends Controller
{
    /**
     * @Route("/comments", name="new_comment")
     * @Method({"POST"})
     */
    public function newCommentAction(Request $request) 
    {
        $post_id = $request->get('post_id');

        // Get the Post Number
        $post = $this->getDoctrine()
                 ->getRepository('AppBundle:Post')
                 ->find($post_id);

        // Need to get the current user based on security acces
        $user = Utilities::getCurrentUser($this);

        // Get the User's IP address
        $comment_ip = Utilities::getCurrentIp($this);
    
        // Get the body of the comment from the request
        $body = $request->get('body');
    
        // We have everything we need now
        // Time to add the post to the database
        try {
            $em = Utilities::getEntityManager($this);
            $comment = new Comment;
            $comment->setPost($post);
            $comment->setBody($body);
            $comment->setIpAddress($comment_ip);
            $comment->setUser($user);
            $em->persist($comment);
            $em->flush();
            return new JsonResponse(array('status' => 200, 'message' => 'Success in posting comments', 'comment_id' => $comment->getId()));
        } catch (\Doctrine\DBAL\DBALException $e) {
            return new JsonResponse(array('status' => 400, 'message' => 'Unable to comment.'));
        }   
    }
    
     /**
     * @Route("/comments/upvote", name="upvote_comment")
     * @Method({"POST"})
     */
    public function upvoteCommentAction(Request $request) 
	{
        // Get post id from the request
        $post_id = $request->get("post_id");
        
        // Get current user
        $user = Utilities::getCurrentUser($this);
        
        // Get the entity manager for Doctrine
        $em = Utilities::getEntityManager($this);

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
            $post->setScore(Utilities::hot($post->getUpvotes(), $post->getDownvotes(), $post->getCreated()));
            $em->persist($post);
            $em->flush();
            $score = ($post->getUpvotes() - $post->getDownvotes());
        } catch (\Docrine\DBAL\DBALException $e) {
            return new JsonResponse(array('status' => 400, 'message' => 'Unable to add like. $e->message'));
        }
        
        return new JsonResponse(array('status' => 200, 'message' => 'Success on upvote.', 'score' => $score));
	}
    
    /**
     * @Route("/comments/downvote", name="downvote_comment")
     * @Method({"POST"})
     */
    public function downvoteCommentAction(Request $request) 
    {
        // Get the post_id
        $post_id = $request->get('post_id');
        
        // Get current user
        $user = Utilities::getCurrentUser($this);
        
        // Get the entity manager
        $em = Utilities::getEntityManager($this);
        
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
            $post->setScore(Utilities::hot($post->getUpvotes(), $post->getDownvotes(), $post->getCreated()));
            $em->persist($post);
            $em->flush();
            $score = ($post->getUpvotes() - $post->getDownvotes());
        } catch (\Doctrine\DBAL\DBALException $e) {
            return new JsonResponse(array('status' => 400, 'message' => 'Unable to dislike. $e->message'));  
        }

        return new JsonResponse(array('status' => 200, 'message' => 'Success on upvoting', 'score' => $score));
    }
    
    /**
     * @Route("/comments", name="remove")
     * @Method({"DELETE"})
     */
    public function removeCommentAction(Request $request) 
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
            $em = Utilities::getEntityManager($this);
            $em->remove($post);
            $em->flush();
            return new JsonResponse(array('status' => 200, 'message' => 'Success'));
        } catch (\Doctrine\DBAL\DBALException $e) {
            return new JsonResponse(array('status' => 400, 'message' => 'Unable to delete post.'));
        }   
    } 
}