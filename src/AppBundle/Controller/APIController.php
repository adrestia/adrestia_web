<?php

namespace AppBundle\Controller;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\Query;
use AppBundle\Controller\RegistrationController;
use AppBundle\Entity\Post;
use AppBundle\Entity\User;
use AppBundle\Entity\College;
use AppBundle\Entity\EmailAuth;
use AppBundle\Entity\PostLikes;
use AppBundle\Helper\Utilities;
use AppBundle\Entity\Comment;
use AppBundle\Entity\CommentLikes;

/**
 * @Route("/api")
 */
class APIController extends Controller
{   
    /**
     * @Route("/posts/upvote", name="api_upvote_post")
     * @Method({"POST"})
     *
     * @param post_id – id of the post you want to upvote
     * @return JSON – status of result
     */
    public function upvotePostAction(Request $request) 
    {
        // Get post id from the request
        $post_id = $request->request->get("post_id");
        
        // Get current user
        $user = Utilities::getCurrentUser($this);
        
        // Get the entity manager for Doctrine
        $em = Utilities::getEntityManager($this);

        // Get the post from the post_id in the database
        $post = $this->getDoctrine()
                     ->getRepository('AppBundle:Post')
                     ->find($post_id);
        
        // get post user
        $post_user = $post->getUser();
    
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
                $like->setUser($user);
                $like->setPost($post);
                $post->setUpvotes($post->getUpvotes() + 1);
                $post_user->setScore($post_user->getScore() + 1);
                $post->addLike($like);
                $em->persist($like);
            } else {
                if($like->getIsLike()) {
                    $post->setUpvotes($post->getUpvotes() - 1);
                    $post_user->setScore($post_user->getScore() - 1);
                    $post->removeLike($like);
                    $em->remove($like);
                } else {
                    $post->setUpvotes($post->getUpvotes() + 1);
                    $post->setDownvotes($post->getDownvotes() - 1);
                    $post_user->setScore($post_user->getScore() + 2);
                    $like->setIsLike(true);
                    $em->persist($like);
                }
            }
            $post->setScore(Utilities::hot($post->getUpvotes(), $post->getDownvotes(), $post->getCreated()));
            $em->persist($post_user);
            $em->persist($post);
            $em->flush();
            $score = ($post->getUpvotes() - $post->getDownvotes());
        } catch (\Docrine\DBAL\DBALException $e) {
            return new JsonResponse(array('status' => 400, 'message' => 'Unable to add like.' . $e->message));
        }
        
        return new JsonResponse(array('status' => 200, 'message' => 'Success on upvote.', 'score' => $score));
    }
    
    /**
     * @Route("/posts/downvote", name="api_downvote_post")
     * @Method({"POST"})
     *
     * @param post_id – id of the post you want to downvote
     * @return JSON – status of action
     */
    public function downvotePostAction(Request $request) 
    {
       // Get the post_id
       $post_id = $request->request->get("post_id");
   
       // Get current user
       $user = Utilities::getCurrentUser($this);
   
       // Get the entity manager
       $em = Utilities::getEntityManager($this);
   
       // Get the post from the post_id in the database
       $post = $em->getRepository('AppBundle:Post')
                  ->find($post_id);
       
       // get post user
       $post_user = $post->getUser();
   
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
               $dislike->setUser($user);
               $dislike->setPost($post);
               $post->setDownvotes($post->getDownvotes() + 1);
               $post_user->setScore($post_user->getScore() - 1);
               $post->addLike($dislike);
               $em->persist($dislike);
           } else {
               if($like->getIsLike()) {
                   $post->setUpvotes($post->getUpvotes() - 1);
                   $post->setDownvotes($post->getDownvotes() + 1);
                   $post_user->setScore($post_user->getScore() - 2);
                   $like->setIsLike(false);
                   $em->persist($like);
               } else {
                   $post->setDownvotes($post->getDownvotes() - 1);
                   $post_user->setScore($post_user->getScore() + 1);
                   $em->remove($like);
                   $post->removeLike($like);
               }
           }
           $post->setScore(Utilities::hot($post->getUpvotes(), $post->getDownvotes(), $post->getCreated()));
           $em->persist($post_user);
           $em->persist($post);
           $em->flush();
           $score = ($post->getUpvotes() - $post->getDownvotes());
       } catch (\Doctrine\DBAL\DBALException $e) {
           return new JsonResponse(array('status' => 400, 'message' => 'Unable to dislike.' . $e->message));  
       }
   
       return new JsonResponse(array('status' => 200, 'message' => 'Success on downvoting', 'score' => $score));
    }

    /**
     * @Route("/comments/upvote", name="api_upvote_comment")
     * @Method({"POST"})
     *
     * @param comment_id – id of the coment you want to upvote
     * @return JSON – status of action
     */
    public function upvoteCommentAction(Request $request) 
    {
       // Get comment id from the request
       $comment_id = $request->get("comment_id");

       // Get current user
       $user = Utilities::getCurrentUser($this);

       // Get the entity manager for Doctrine
       $em = Utilities::getEntityManager($this);

       // Get the comment from the comment_id in the database
       $comment = $em->getRepository('AppBundle:Comment')
                     ->find($comment_id);
       
       // Comment user
       $comment_user = $comment->getUser();
       
       // If anything other than a comment is returned (including null)
       // throw an error.
       if (!$comment) {
           throw $this->createNotFoundException(
                'No comment found for id ' . $id
            );
        }

        try {
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
            return new JsonResponse(array('status' => 400, 'message' => 'Unable to add like.' . $e->message));
        }

        return new JsonResponse(array('status' => 200, 'message' => 'Success on upvote.', 'score' => $score));
    }

    /**
     * @Route("/comments/downvote", name="api_downvote_comment")
     * @Method({"POST"})
     *
     * @param comment_id – id of the comment you want to downvote
     * @return JSON – status of action
     */
    public function downvoteCommentAction(Request $request) 
    {
       // Get the comment_id
       $comment_id = $request->get('comment_id');

       // Get current user
       $user = Utilities::getCurrentUser($this);

       // Get the entity manager
       $em = Utilities::getEntityManager($this);

       // Get the comment from the comment_id in the database
       $comment = $em->getRepository('AppBundle:Comment')
                     ->find($comment_id);
       
       // Comment user
       $comment_user = $comment->getUser();

      // If anything other than a comment is returned (including null)
      // throw an error.
      if (!$comment) {
          throw $this->createNotFoundException(
               'No post comment for id ' . $id
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
               $comment_user->setScore($comment_user->getScore() - 1);
               $comment->addLike($dislike);
               $em->persist($dislike);
           } else {
               if($like->getIsLike()) {
                   $comment->setUpvotes($comment->getUpvotes() - 1);
                   $comment->setDownvotes($comment->getDownvotes() + 1);
                   $comment_user->setScore($comment_user->getScore() - 2);
                   $like->setIsLike(false);
                   $em->persist($like);
               } else {
                   $comment->setDownvotes($comment->getDownvotes() - 1);
                   $comment_user->setScore($comment_user->getScore() + 1);
                   $em->remove($like);
                   $comment->removeLike($like);
               }
           }
           $em->persist($comment_user);
           $em->persist($comment);
           $em->flush();
           $score = ($comment->getUpvotes() - $comment->getDownvotes());
       } catch (\Doctrine\DBAL\DBALException $e) {
           return new JsonResponse(array('status' => 400, 'message' => 'Unable to dislike comment' . $e->message));  
       }

       return new JsonResponse(array('status' => 200, 'message' => 'Success on upvoting', 'score' => $score));
    }
    
    /**
     * @Route("/posts", name="api_new_post")
     * @Method({"POST"})
     */
    public function newPostAction(Request $request) 
    {
        // Get the User's IP address
        $post_ip = Utilities::getCurrentIp($this);

        // Need to get the current user based on security acces
        $user = Utilities::getCurrentUser($this);

        // Get the body of the post from the request
        $body = $request->get('body');
        
        if(trim($body) === '') {
            return new JsonResponse(array('status' => 400, 'message' => "Empty body"));
        }
    
        $body = preg_replace("/[\r\n]{2,}/", "\n\n", $body); 

        // We have everything we need now
        // Time to add the post to the database
        try {
            $em = Utilities::getEntityManager($this);
            $post = new Post;
            $post->setBody($body);
            $post->setScore(Utilities::hot(0, 0, new \DateTime('NOW')));
            $post->setIpAddress($post_ip);
            $post->setCollege($user->getCollege());
            $post->setUser($user);
            $em->persist($post);
            $em->flush();
            return new JsonResponse(array('status' => 200, 'message' => 'Success', 'post_id' => $post->getId()));
        } catch (\Doctrine\DBAL\DBALException $e) {
            return new JsonResponse(array('status' => 400, 'message' => 'Unable to submit post.'));
        }
    }

    /**
     * @Route("/{sorting}", name="api_home", requirements={"sorting":"top|new|hot"})
     * @Method({"GET"})
     *
     * @return JSON – serialized list of posts
     */
    public function indexAction(Request $request, $sorting)
    {
        $em = Utilities::getEntityManager($this);
    
        $user = Utilities::getCurrentUser($this);
    
        /*
        EQUIVALENT QUERY TO BUILDER BELOW

       "SELECT p.id, p.user_id, p.body, p.upvotes, 
                p.downvotes, p.score, p.reports, 
                p.created, l.is_like, l.user_id, 
                l.post_id, (p.upvotes - p.downvotes) AS top
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
            ->select('partial p.{id, body, upvotes, downvotes, score, reports, created}', 'IDENTITY(p.user)')
            ->addSelect('(p.upvotes - p.downvotes) AS HIDDEN top')
            ->from('AppBundle:Post', 'p') 
            ->where('p.college = :college AND p.hidden = false')
            ->setParameter('college', $user->getCollege())
            ->leftJoin('p.likes',
                'l',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'p.id = l.post AND l.user = :user'
                )
            ->setParameter('user', $user->getId())
            ->addSelect('l AS likes')
            ->leftJoin('p.comments', 'c')
            ->addSelect('COUNT(c.id) AS comments')
            ->setMaxResults(100)
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
            
        $posts = $builder->getQuery()->getScalarResult();
        $serializer = $this->container->get('serializer');
        $reports = $serializer->serialize($posts, 'json');
        return new Response($reports, 200);
    }

    /**
     * @Route("/posts/{post_id}", name="get_post", requirements={"post_id" = "\d+"})
     * @Method({"GET"})
     *
     * @param URL post_id – id of post you want to get
     * @return JSON – seralized post
     */
    public function getPostAction(Request $request, $post_id)
    {
        $em = Utilities::getEntityManager($this);
        $user = Utilities::getCurrentUser($this);
        
        // First get the post
        $post = $em->getRepository('AppBundle:Post')
                   ->findOneBy(array('id' => $post_id, 'hidden' => false));
        
        // If anything other than a post is returned (including null)
        // throw an error.
        if (!$post) {
            return new JsonResponse(array('message' => "Post not found for id " . $post_id), 400);
        }
        
        // If this is a post from another college, redirect the user
        // to the no-participation link of the post
        if($post->getCollege() !== $user->getCollege()) {
            return new JsonResponse(array('message' => "User is not part of this college"), 401);
        }
        
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
            ->select('partial c.{id, body, created, downvotes, ip_address, reports, upvotes}')
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
            ->addSelect('l AS likes')
            ->groupBy('c', 'l')
            ->orderBy('c.created', 'ASC');
                
        $raw_comments = $builder->getQuery()->getScalarResult();
        
        // Get Scalar of the like 
        $builder = $em->createQueryBuilder();
        $builder
            ->select('l')
            ->from('AppBundle:PostLikes', 'l') 
            ->where('l.post = :postid AND l.user = :user')
            ->setParameter('postid', $post->getId())
            ->setParameter('user', $user->getId())
            ->setMaxResults(1);
        $like = $builder->getQuery()->getScalarResult();
        
        $like = empty($like) ? false : true;
        
        // Get Scalar of the post 
        $builder = $em->createQueryBuilder();
        $builder
            ->select('p')
            ->from('AppBundle:Post', 'p') 
            ->where('p.id = :postid AND p.hidden = false')
            ->setParameter('postid', $post_id)
            ->setMaxResults(1);
        $post = $builder->getQuery()->getScalarResult();
        $post = $post[0];
        
        return new JsonResponse(
            array(
                'post'      => $post,
                'like'      => $like,
                'comments'  => $raw_comments,
            ), 200
        );
    }

    /**
     * @Route("/posts", name="api_remove_post")
     * @Method({"DELETE"})
     *
     * @param post_id – id of the post you want to delete
     * @return JSON – status of action
     */
    public function removePostAction(Request $request) 
    {
         // Get post id from the request
        $post_id = $request->request->get("post_id");

        // Get the post from the post_id in the database
        $post = $this->getDoctrine()
                     ->getRepository('AppBundle:Post')
                     ->find($post_id);

        // If anything other than a post is returned (including null)
        // throw an error.
        if (!$post) {
            return new Response("No post found for id " . $post_id, 400);
        }
    
        if($post->getUser() !== Utilities::getCurrentUser($this)) {
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
            $post->setHidden(true);
            $em->persist($post);
            $em->flush();
            return new JsonResponse(array('status' => 200, 'message' => 'Success'));
        } catch (\Doctrine\DBAL\DBALException $e) {
            return new JsonResponse(array('status' => 400, 'message' => 'Unable to delete post.'));
        }   
    }

    /**
     * @Route("/comments", name="api_new_comment")
     * @Method({"POST"})
     *
     * @param post_id – id of the post you want to add the comment to
     * @param body – body text of the comment
     * @return JSON – status of message, resultant comment_id, and boolean is_op
     */
    public function newCommentAction(Request $request) 
    {
        $post_id = $request->request->get("post_id");

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
     * @Route("/register", name="api_register")
     * @Method({"POST"})
     *
     * @param email – email of the user to register
     * @param password – password of the user 
     * @param college_name – name of the college (name from the database!!)
     * @return JSON – status of the action
     */
    public function registerAction(Request $request)
    {
        $email = $request->get('email');
        $plain_password = $request->get('password');
        $college_name = $request->get('college_name');
    
        // Validation checks
        $emailConstraint = new EmailConstraint();
        $emailConstraint->message = "Not a valid email address.";
    
        $errors = $this->get('validator')->validate(
            $email,
            $emailConstraint 
        );
    
        // Make sure email is valid
        if(count($errors) > 0) {
            return new JsonResponse(
                array(
                    'status' => 400, 
                    'error' => (string)$errors,
                )
            );
        }
    
        // Make sure email ends in .edu
        $edu_pattern = "/\.edu$/";
        if(!preg_match($edu_pattern, $email)) {
            return new JsonResponse(
                array(
                    'status' => 400, 
                    'error' => $email . ' is not a .edu email address.'
                )
            );
        }
    
        // Make sure password is at least 8 characters
        if(strlen($plain_password) < 8) {
            return new JsonResponse(
                array(
                    'status' => 400, 
                    'error' => 'Password must be at least 8 characters.'
                )
            );
        }

        $em = Utilities::getEntityManager($this);
    
        // Make sure user doesn't already exist in the database
        $user = $em->getRepository('AppBundle:User')
                   ->findOneBy(array('email' => $email));
    
        if($user) {
            return new JsonResponse(
                array(
                    'status' => 400, 
                    'error' => 'User already exists.'
                )
            ); 
        }
    
        // Passed validation. Create the user.
        $user = new User();
    
        $user->setEmail($email);
    
        // Encode the password
        $password = $this->get('security.password_encoder')
            ->encodePassword($user, $plain_password);
        $user->setPassword($password);
    
        // Set the college of the user
        $college = $em->getRepository('AppBundle:College')
                      ->findOneBy(array('name' => $college_name));
    
        if(!$college) {
            return new JsonResponse(
                array(
                    'status' => 500, 
                    'error' => 'Error looking up college in database.'
                )
            );
        }
    
        $user->setCollege($college);
    
        // Get a unique API key
        do { 
            $apikey = Utilities::guidv4();
            $entity = $em->getRepository('AppBundle\Entity\User')->findOneBy(array('api_key' => $apikey));
        } while($entity !== null);
    
        // Set their API key
        $user->setApiKey($apikey);
    
        // Create an email confirmation token
        $email_auth = new EmailAuth();
    
        // Generate a new token for confirmation
        do {
            $token = Utilities::guidv4();
            $entity = $em->getRepository('AppBundle:EmailAuth')->findOneBy(array('token' => $token));
        } while($entity !== null);
    
        // Configure the confirmation token
        $email_auth->setToken($token);
        $email_auth->setUser($user);
    
        // Save the user
        $em->persist($user);
        $em->persist($email_auth);
        $em->flush();
    
        // Send the confirmation email
        $sent = Utilities::sendEmail($user->getEmail(), $token, $this);
    
        if($sent) {
            return new JsonResponse(
                array(
                    'status' => 200, 
                    'message' => 'Email has been sent to ' . $email,
                )
            ); 
        } else {
            return new JsonResponse(
                array(
                    'status' => 500, 
                    'error' => 'Unable to send email.'
                )
            ); 
        }
    }

    /**
     * @Route("/login", name="api_login")
     * @Method({"POST"})
     *
     * @param email – email of the user to login
     * @param password – password of the user to login
     * @return JSON – API key of the logged-in user
     */
    public function loginAction(Request $request)
    {
        $email = $request->get('email');
        $plain_password = $request->get('password');
    
        // Validation checks
        $emailConstraint = new EmailConstraint();
        $emailConstraint->message = "Not a valid email address.";
    
        $errors = $this->get('validator')->validate(
            $email,
            $emailConstraint 
        );
    
        // Make sure email is valid
        if(count($errors) > 0) {
            return new JsonResponse(
                array(
                    'status' => 400, 
                    'error' => (string)$errors,
                )
            );
        }
    
        // Make sure email ends in .edu
        $edu_pattern = "/\.edu$/";
        if(!preg_match($edu_pattern, $email)) {
            return new JsonResponse(
                array(
                    'status' => 400, 
                    'error' => $email . ' is not a .edu email address.'
                )
            );
        }
    
        // Make sure password is at least 8 characters
        if(strlen($plain_password) < 8) {
            return new JsonResponse(
                array(
                    'status' => 400, 
                    'error' => 'Password must be at least 8 characters.'
                )
            );
        }
    
        // Password and Email have passed basic checks
        // Time to find the data
        $em = Utilities::getEntityManager($this);
    
        // Find a user
        $user = $em->getRepository('AppBundle:User')
                   ->findOneBy(array('email' => $email));
    
        if(!$user) {
            return new JsonResponse(
                array(
                    'status' => 401, 
                    'error' => 'Username or password incorrect.'
                )
            ); 
        }
        
        if(!$user->getIsConfirmed()) {
            return new JsonResponse(
                array(
                    'status' => 401, 
                    'error' => 'Email address not confirmed.'
                )
            ); 
        }
    
        if(password_verify($plain_password, $user->getPassword())) {
            return new JsonResponse(
                array(
                    'status' => 200, 
                    'api_key' => $user->getApiKey(),
                )
            );
        } else {
            return new JsonResponse(
                array(
                    'status' => 401, 
                    'error' => 'Username or password incorrect.',
                )
            );
        }
    }

    /**
     * @Route("/profile", name="profile_api")
     * @Method({"GET"})
     *
     * @return JSON – seralized user
     */
    public function profileAction(Request $request) 
    {
      $em = Utilities::getEntityManager($this);
      $user = Utilities::getCurrentUser($this);

      $serializer = $this->container->get('serializer');
      $user = $serializer->serialize($user, 'json');
          
      return new JsonResponse(
              array(
                  'status' => 200, 
                  'user' => $user
              )
          );
    }
}
