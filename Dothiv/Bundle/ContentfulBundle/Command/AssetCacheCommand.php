<?php

namespace Dothiv\Bundle\ContentfulBundle\Command;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use Dothiv\Bundle\ContentfulBundle\Adapter\HttpClientAdapter;
use Dothiv\Bundle\ContentfulBundle\Exception\RuntimeException;
use Dothiv\Bundle\ContentfulBundle\Logger\OutputInterfaceLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AssetCacheCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('contentful:assets:cache')
            ->setDescription('Cache assets');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $adapter \Dothiv\Bundle\ContentfulBundle\Adapter\ContentfulAssetAdapterInterface */
        /** @var $assetRepo \Dothiv\Bundle\ContentfulBundle\Repository\ContentfulAssetRepositoryInterface */
        $adapter   = $this->getContainer()->get('dothiv_contentful.asset');
        $assetRepo = $this->getContainer()->get('dothiv_contentful.repo.asset');
        $adapter->setLogger(new OutputInterfaceLogger($output));
        foreach ($assetRepo->findAll() as $asset) {
            $adapter->cache($asset);
        }
    }
} 
