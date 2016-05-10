<?php
# src/Acme/DemoBundle/Command/CreateClientCommand.php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\College;

class PopulateCollegeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('adrestia:add-colleges')
            ->setDescription('Goes through the JSON list of colleges from the US and adds them to the database.')
            ->addArgument(
                  'college_list',
                  InputArgument::OPTIONAL,
                  'Please supply a JSON list of colleges.'
              );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $list = $input->getArgument('college_list');
        
        $raw_json_string = file_get_contents($list);
        $json = json_decode($raw_json_string);
        
        $em = $this->getContainer()->get('doctrine')->getManager();
        
        $batch_size = 10;
        $i = 0;
        
        foreach($json as $key => $val) {
            try {
                if(@$val->alpha_two_code === "US") {
                    $college = new College();
                    $college->setName($val->name);
                    $college->setSuffix($val->domain);
                    $em->persist($college);
                    if(($i % $batch_size) === 0) {
                        $em->flush();
                        $em->clear();
                    }
                    ++$i;
                }
                $em->flush();
                $em->clear();
            } catch (Exception $e) {
                $output->writeln("Error with " . var_dump($val, true) . ". Message: " . $e->getMessage());
            }
        }
        $output->writeln("Successfully inserted all colleges.");
    }
}