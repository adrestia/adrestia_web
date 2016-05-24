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
 * @Route("/")
 */
class DefaultController extends Controller
{   
    /**
     * @Route("/{sorting}", name="homepage", defaults={"sorting":"hot"}, requirements={"sorting":"top|new|hot|^$"})
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
        
                $em = Utilities::getEntityManager($this);
        
                // Encode the password
                $password = $this->get('security.password_encoder')
                    ->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($password);

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
                Utilities::sendEmail($user->getEmail(), $token, $this);
        
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
            ->select('partial p.{id, body, upvotes, downvotes, score, reports, created}', 'IDENTITY(p.user)')
            ->addSelect('SUM(p.upvotes - p.downvotes) AS HIDDEN top')
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
            ->setMaxResults(50)
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
        
        return $this->render('default/index.html.twig', [
            'posts' => $posts,
            'sorting' => $sorting
        ]);
    }
    
    /**
     * @Route("/{sorting}", name="load_more", defaults={"sorting":"hot"}, requirements={"sorting":"top|new|hot|^$"})
     * @Method({"POST"})
     * 
     * @param offset – offset of posts
     * @return JSON list of posts 
     */
    public function loadMoreAction(Request $request, $sorting)
    {   
        $offset = $request->get('offset');
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
            ->select('partial p.{id, body, upvotes, downvotes, score, reports, created}', 'IDENTITY(p.user)')
            ->addSelect('SUM(p.upvotes - p.downvotes) AS HIDDEN top')
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
            ->setMaxResults(25)
            ->setFirstResult($offset)
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
                
        try {
            $posts = $builder->getQuery()->getScalarResult();
        } catch(\Docrine\DBAL\DBALException $e) {
            return new Response($reports, 500);
        }
        $serializer = $this->container->get('serializer');
        $reports = $serializer->serialize($posts, 'json');
        return new Response($reports, 200);
    }
    
    /**
     * @Route("/colleges", name="college_home")
     */
    public function collegePickAction(Request $request)
    {
        $em = Utilities::getEntityManager($this);
        
        $user = Utilities::getCurrentUser($this);
        
        /*
        EQUIVALENT QUERY TO BUILDER BELOW

       "SELECT p.id, p.user_id, p.body, p.upvotes, 
                p.downvotes, p.score, p.reports, 
                p.created, SUM(p.upvotes - p.downvotes) AS top
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
            ->addSelect('SUM(p.upvotes - p.downvotes) AS HIDDEN top')
            ->from('AppBundle:Post', 'p') 
            ->where('p.hidden = false')
            ->groupBy('p')
            ->setMaxResults(25)
            ->orderBy('p.score', 'DESC');
        
        $posts = $builder->getQuery()->getResult();
        
        
        // Simply selecting all colleges
        $builder = $em->createQueryBuilder()->select('c')->from('AppBundle:College', 'c');
        $colleges = $builder->getQuery()->getResult();
                
        return $this->render('default/college_view.html.twig', [
            'posts' => $posts,
            'colleges' => $colleges,
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
     * @Route("/forgot", name="forgot_password")
     */
    public function forgotPasswordAction(Request $request) {
        if($request->getMethod() === "POST") {
        
            $email = $request->get('email');
            
            $em = Utilities::getEntityManager($this);    
            $user = $em->getRepository('AppBundle:User')->findOneBy(array('email' => $email));
            
            if(!$user) {
                return $this->render(
                    'security/forgot.html.twig', array(
                        'error' => "Email not found.",
                    )
                );
            }
            
            $password_auth = new PasswordAuth();
            
            do {
                $token = Utilities::guidv4();
                $entity = $em->getRepository('AppBundle:PasswordAuth')->findOneBy(array('token' => $token));
            } while($entity !== null);
            
            $password_auth->setToken($token);
            $password_auth->setUser($user);
            $em->persist($password_auth);
            $em->flush();
            
            $message = \Swift_Message::newInstance()
                    ->setSubject('Password Reset')
                    ->setFrom(array('adrestiaweb@gmail.com' => 'College Confessions'))
                    ->setTo($email)
                    ->setBody(
                        $this->renderView(
                            // app/Resources/views/Emails/registration.html.twig
                            'emails/password.html.twig',
                            array('token' => $token)
                        ),
                        'text/html'
                    )
                    ->addPart(
                        $this->renderView(
                            // This is the txt version (non-HTML)
                            'emails/registration.txt.twig',
                            array('token' => $token)
                        ),
                        'text/plain'
                    );
            if($this->get('mailer')->send($message)) {
                return $this->render(
                    'security/forgot.html.twig', array(
                        'flash' => "Email sent!",
                    )
                );
            } else {
                return $this->render(
                    'security/forgot.html.twig', array(
                        'error' => "Unable to send email",
                    )
                );
            }
        }
        
        return $this->render(
            'security/forgot.html.twig'
        );
    }
    
    /**
     * @Route("/password", name="reset_password")
     */
    public function resetPasswordAction(Request $request) {
        $token = $request->get('token');
        
        if(empty($token)) {
            return $this->redirect($this->generateUrl('forgot_password'));
        }
        
        $em = Utilities::getEntityManager($this);
        
        $token = $em->getRepository('AppBundle:PasswordAuth')
                    ->findOneBy(['token' => $token]);
        
        if(!$token) {
            return $this->render(
                'security/password.html.twig', array(
                    'error' => "Invalid password reset token. Please check the link and try again.",
                )
            );
        }
        
        if($token->getUsed()) {
            return $this->render(
                'security/password.html.twig', array(
                    'error' => "This password reset token has already been used.",
                )
            );
        }
        
        $resetPasswordModel = new ResetPassword();
        $form = $this->createForm(ResetPasswordType::class, $resetPasswordModel);
        $form->handleRequest($request);
        $user = $token->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $resetPasswordModel->getNewPassword());
            $user->setPassword($password);
            $user->setUpdated(new \DateTime);
            $token->setUsed(true);
            $em->persist($token);
            $em->persist($user);
            $em->flush();
            
            return $this->render(
                'security/password.html.twig', array(
                    'form' => $form->createView(),
                    'flash' => "Successfully Reset Password!",
                )
            );
        }
        
        return $this->render(
            'security/password.html.twig', array(
                'form' => $form->createView(),
            )
        );
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
