<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\User;
use AppBundle\Entity\Role;

class SetRoleCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('adrestia:set-role')
            ->setDescription('Changes a users role to the specified role')    
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'Please put in an email address for the user.'  
            )
            ->addArgument(
                'role',
                InputArgument::REQUIRED,
                'Add in the role value ["ROLE_USER", "ROLE_ADMIN", or "ROLE_SUPER_ADMIN"]'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        $role = $input->getArgument('role');
        
        $em = $this->getContainer()->get('doctrine')->getManager();
        
        $role_obj = $em->getRepository("AppBundle:Role")
                       ->findOneBy(['role' => $role]);
        
        $user = $em->getRepository('AppBundle:User')
                   ->findOneBy(['email' => $email]);
        
        $user->setRoles([$role_obj]);
         
         // Save the user
         $em->persist($user);
         $em->flush();
        
        $output->writeln("Successfully set $email to $role");
    }
}