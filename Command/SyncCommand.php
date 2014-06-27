<?php

namespace Dothiv\ContentfulBundle\Command;

use Doctrine\ORM\EntityManager;
use Dothiv\ContentfulBundle\Logger\OutputInterfaceLogger;
use Dothiv\ContentfulBundle\Task\SyncTask;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('contentful:sync')
            ->setDescription('Sync entries from a space')
            ->addOption('space', 'S', InputOption::VALUE_REQUIRED, 'ID of the space', 'cfexampleapi')
            ->addOption('access_token', 't', InputOption::VALUE_REQUIRED, 'Access token', 'b4c0n73n7fu1');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        /** @var SyncTask $syncTask */
        $em       = $this->getContainer()->get('doctrine.orm.entity_manager');
        $syncTask = $this->getContainer()->get('dothiv_contentful.task.sync');

        $syncTask->setLogger(new OutputInterfaceLogger($output));
        $syncTask->sync($input->getOption('space'), $input->getOption('access_token'));

        $em->flush();
    }
}
