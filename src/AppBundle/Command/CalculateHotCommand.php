<?php
# src/Acme/DemoBundle/Command/CreateClientCommand.php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\Post;

class CalculateHotCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('adrestia:process-hot')
            ->setDescription('Iterates over the database and calculates the hot score.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $batch_size = 10;
        $i = 0;
        
        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);
        
        $query = $em->createQuery('select p from AppBundle:Post p');
        $iterable_result = $query->iterate();
        
        foreach($iterable_result as $post) {
            $post = $post[0];
            $score = self::hot($post->getUpvotes(), $post->getDownvotes(), $post->getCreated());
            $post->setScore($score);
            if(($i % $batch_size) === 0) {
                $em->flush();
                $em->clear();
            }
            ++$i;
        }
        $em->flush();
        $em->clear();
    }
    
    /**
     * The reddit hotness algorithm!
     *
     * @param $ups – Number of post upvotes
     * @param $downs – Number of post downvotes
     * @param $data – When the post was submitted
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