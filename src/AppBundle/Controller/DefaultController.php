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
use AppBundle\Form\Model\ChangePassword;
use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use AppBundle\Entity\Post;
use AppBundle\Entity\PostLikes;
use AppBundle\Entity\Comment;
use AppBundle\Helper\Utilities;

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
        // Need to see if there is a user that is logged in
        // If not, then present them with the homepage :)
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            
            $user = new User();
            $form = $this->createForm(UserType::class, $user);
    
            // Handle Request
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
        
                $em = $this->getDoctrine()->getManager();
        
                // Encode the password
                $password = $this->get('security.password_encoder')
                    ->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($password);

                // Get a unique API key
                do {
                    $apikey = self::guidv4();
                    $entity = $em->getRepository('AppBundle\Entity\User')->findOneBy(array('api_key' => $apikey));
                } while($entity !== null);
        
                // Set their API key
                $user->setApiKey($apikey);
        
                // Create an email confirmation token
                $email_auth = new EmailAuth();
        
                // Generate a new token for confirmation
                do {
                    $token = self::guidv4();
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
                self::sendEmail($user->getEmail(), $token);
        
                // Show the confirmation email
                return $this->render(
                    'registration/confirm.html.twig', [
                        'email' => $user->getEmail()
                    ]
                );
            }
        
            return $this->render('default/home.html.twig', array(
                'form' => $form->createView(),
            )); 
        }
        
        $em = Utilities::getEntityManager($this);
        
        $user = Utilities::getCurrentUser($this);
        
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
     * @Route("/college/{college_id}/{sorting}", name="college_page", defaults={"sorting":"hot"}, requirements={"sorting":"top|new|hot|^$", "college_id" : "\d+"})
     */
    public function collegePageAction(Request $request, $college_id, $sorting)
    {
        $em = Utilities::getEntityManager($this);
        $user = Utilities::getCurrentUser($this);
        
        /*
        EQUIVALENT QUERY TO BUILDER BELOW

       "SELECT p.id, p.user_id, p.body, p.upvotes, 
                p.downvotes, p.score, p.reports, 
                p.created, SUM(p.upvotes - p.downvotes) AS top
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
            ->addSelect('SUM(p.upvotes - p.downvotes) AS HIDDEN top')
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
        
        return $this->render('default/college.html.twig', [
            'posts' => $posts,
            'sorting' => $sorting,
            'college' => $college,
        ]);
    }
    
    /**
     * @Route("/profile", name="profile")
     */
    public function profileAction(Request $request) 
    {
        $changePasswordModel = new ChangePassword();
        $form = $this->createForm(ChangePasswordType::class, $changePasswordModel);

        $form->handleRequest($request);
        
        $em = Utilities::getEntityManager($this);
        $user = Utilities::getCurrentUser($this);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $changePasswordModel->getNewPassword());
            $user->setPassword($password);
            $user->setUpdated(new \DateTime);
            $em->persist($user);
            $em->flush();
            
            return $this->render(
                'default/profile.html.twig', array(
                    'form' => $form->createView(),
                    'flash' => "Successfully Updated Password!",
                )
            );
        }

        return $this->render(
            'default/profile.html.twig', array(
                'form' => $form->createView(),
            )
        );
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
}
