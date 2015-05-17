<?php

namespace Dothiv\Bundle\ContentfulBundle\Command;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use Dothiv\Bundle\ContentfulBundle\Logger\OutputInterfaceLogger;
use Dothiv\Bundle\ContentfulBundle\Output\ImageAssetScaler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ScaleAssetsImagesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('contentful:images:scale')
            ->setDescription('Generate scaled version of all contentful image assets');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $assetRepo \Dothiv\Bundle\ContentfulBundle\Repository\ContentfulAssetRepositoryInterface */
        /** @var $imageScaler ImageAssetScaler */
        $assetRepo   = $this->getContainer()->get('dothiv_contentful.repo.asset');
        $imageScaler = $this->getContainer()->get('dothiv_contentful.image_asset_scaler');
        $imageScaler->setLogger(new OutputInterfaceLogger($output));
        foreach ($assetRepo->findAll() as $asset) {
            $imageScaler->scaleAsset($asset);
        }
    }
} 
