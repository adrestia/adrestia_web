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
use AppBundle\Entity\Comment;
use AppBundle\Entity\Post;
use AppBundle\Entity\CommentLikes;
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
        
        if(trim($body) === '') {
            return new JsonResponse(array('status' => 400, 'message' => "Empty body"));
        }
        
        $body = preg_replace("/[\r\n]{2,}/", "\n\n", $body); 
        
        // Check if the person commenting is the OP on the post
        $is_op = $post->getUser()->getId() === $user->getId();
    
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
            return new JsonResponse(array('status' => 200, 'message' => 'Success in posting comments', 'comment_id' => $comment->getId(), 'is_op' => $is_op));
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
        $comment_id = $request->get("comment_id");
        
        // Get current user
        $user = Utilities::getCurrentUser($this);
        
        // Get the entity manager for Doctrine
        $em = Utilities::getEntityManager($this);

		// Get the post from the post_id in the database
        $comment = $em->getRepository('AppBundle:Comment')
                      ->find($comment_id);
        
        // Need the comment user
        $comment_user = $comment->getUser();
    
        // If anything other than a post is returned (including null)
        // throw an error.
        if (!$comment) {
            throw $this->createNotFoundException(
                'No comment found for id ' . $id
            );
        }

        try{
            $like = $em->getRepository('AppBundle:CommentLikes')
                       ->findOneBy(array('comment' => $comment_id, 'user' => $user->getId()));
            
            if(!isset($like)) {
                $like = new CommentLikes;
                $like->setIsLike(true);
                $like->setUser($user);
                $like->setComment($comment);
                $comment->setUpvotes($comment->getUpvotes() + 1);
                $comment_user->setScore($comment_user->getScore() + 1);
                $comment->addLike($like);
                $em->persist($like);
            } else {
                if($like->getIsLike()) {
                    $comment->setUpvotes($comment->getUpvotes() - 1);
                    $comment_user->setScore($comment_user->getScore() - 1);
                    $comment->removeLike($like);
                    $em->remove($like);
                } else {
                    $comment->setUpvotes($comment->getUpvotes() + 1);
                    $comment->setDownvotes($comment->getDownvotes() - 1);
                    $comment_user->setScore($comment_user->getScore() + 2);
                    $like->setIsLike(true);
                    $em->persist($like);
                }
            }
            $em->persist($comment_user);
            $em->persist($comment);
            $em->flush();
            $score = ($comment->getUpvotes() - $comment->getDownvotes());
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
        $comment_id = $request->get('comment_id');
        
        // Get current user
        $user = Utilities::getCurrentUser($this);
        
        // Get the entity manager
        $em = Utilities::getEntityManager($this);
        
        // Get the post from the post_id in the database
        $comment = $em->getRepository('AppBundle:Comment')
                      ->find($comment_id);
        
        // If anything other than a post is returned (including null)
        // throw an error.
        if (!$comment) {
            throw $this->createNotFoundException(
                'No post found for id ' . $id
            );
        }

        // Try to add the downvote
        try {
            $like = $em->getRepository('AppBundle:CommentLikes')
                       ->findOneBy(array('comment' => $comment_id, 'user' => $user->getId()));

            if(!isset($like)) {
                $dislike = new CommentLikes;
                $dislike->setIsLike(false);
                $dislike->setUser($user);
                $dislike->setComment($comment);
                $comment->setDownvotes($comment->getDownvotes() + 1);
                $user->setScore($user->getScore() - 1);
                $comment->addLike($dislike);
                $em->persist($dislike);
            } else {
                if($like->getIsLike()) {
                    $comment->setUpvotes($comment->getUpvotes() - 1);
                    $comment->setDownvotes($comment->getDownvotes() + 1);
                    $user->setScore($user->getScore() - 2);
                    $like->setIsLike(false);
                    $em->persist($like);
                } else {
                    $comment->setDownvotes($comment->getDownvotes() - 1);
                    $user->setScore($user->getScore() + 1);
                    $em->remove($like);
                    $comment->removeLike($like);
                }
            }
            $em->persist($user);
            $em->persist($comment);
            $em->flush();
            $score = ($comment->getUpvotes() - $comment->getDownvotes());
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
        $comment_id = $request->get("comment_id");

        // Get the post from the post_id in the database
        $comment = $this->getDoctrine()
                        ->getRepository('AppBundle:Comment')
                        ->find($comment_id);
    
        // If anything other than a post is returned (including null)
        // throw an error.
        if (!$comment) {
            throw $this->createNotFoundException(
                'No post found for id ' . $id
            );
        }
        
        if($comment->getUser() !== Utilities::getCurrentUser($this)) {
            return new JsonResponse(
                array(
                    'status' => 403, 
                    'message' => strtr("That is not your comment. -_-"),
                    )
            );
        }
        
        // Time to delete the post to the database
        try {
            $em = Utilities::getEntityManager($this);
            $comment->setHidden(true);
            $em->persist($comment);
            $em->flush();
            return new JsonResponse(array('status' => 200, 'message' => 'Success'));
        } catch (\Doctrine\DBAL\DBALException $e) {
            return new JsonResponse(array('status' => 400, 'message' => 'Unable to delete comment.'));
        }   
    } 
}