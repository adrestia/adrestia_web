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
use AppBundle\Controller\RegistrationController;
use AppBundle\Entity\Post;
use AppBundle\Entity\User;
use AppBundle\Entity\College;
use AppBundle\Entity\EmailAuth;
use AppBundle\Entity\PostLikes;
use AppBundle\Helper\Utilities;

/**
 * @Route("/api")
 */
class APIController extends Controller
{
    /**
     * @Route("/register", name="api_register")
     * @Method({"POST"})
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
            $apikey = RegistrationController::guidv4();
            $entity = $em->getRepository('AppBundle\Entity\User')->findOneBy(array('api_key' => $apikey));
        } while($entity !== null);
        
        // Set their API key
        $user->setApiKey($apikey);
        
        // Create an email confirmation token
        $email_auth = new EmailAuth();
        
        // Generate a new token for confirmation
        do {
            $token = RegistrationController::guidv4();
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
        $sent = RegistrationController::sendEmail($user->getEmail(), $token);
        
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
                   ->findOneBy(array('email' => $email, 'is_active' => 1));
        
        if(!$user) {
            return new JsonResponse(
                array(
                    'status' => 401, 
                    'error' => 'Username or password incorrect.'
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
     * @Route("/posts/{post_id}", name="get_post", requirements={"post_id" = "\d+"})
     */
    public function getPostAction(Request $request, $post_id)
    {
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
        
        $serializer = $this->container->get('serializer');
        $reports = $serializer->serialize($post, 'json');
        return new Response($reports);
    }
      /**
     * @Route("/posts", name="api_remove_post")
     * @Method({"DELETE"})
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
            throw $this->createNotFoundException(
                'No post found for id ' . $id
            );
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
     * @Route("/posts/upvote", name="api_upvote_post")
     * @Method({"POST"})
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
                $user->setScore($user->getScore() + 1);
                $post->addLike($like);
                $em->persist($like);
            } else {
                if($like->getIsLike()) {
                    $post->setUpvotes($post->getUpvotes() - 1);
                    $user->setScore($user->getScore() - 1);
                    $post->removeLike($like);
                    $em->remove($like);
                } else {
                    $post->setUpvotes($post->getUpvotes() + 1);
                    $post->setDownvotes($post->getDownvotes() - 1);
                    $user->setScore($user->getScore() + 2);
                    $like->setIsLike(true);
                    $em->persist($like);
                }
            }
            $post->setScore(Utilities::hot($post->getUpvotes(), $post->getDownvotes(), $post->getCreated()));
            $em->persist($user);
            $em->persist($post);
            $em->flush();
            $score = ($post->getUpvotes() - $post->getDownvotes());
        } catch (\Docrine\DBAL\DBALException $e) {
            return new JsonResponse(array('status' => 400, 'message' => 'Unable to add like. $e->message'));
        }
        
        return new JsonResponse(array('status' => 200, 'message' => 'Success on upvote.', 'score' => $score));
    }
}