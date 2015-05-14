<?php

namespace Dothiv\Bundle\ContentfulBundle\Command;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use Dothiv\Bundle\ContentfulBundle\Adapter\HttpClientAdapter;
use Dothiv\Bundle\ContentfulBundle\Logger\OutputInterfaceLogger;
use Dothiv\Bundle\ContentfulBundle\Repository\ContentfulAssetRepositoryInterface;
use Dothiv\Bundle\ContentfulBundle\Repository\ContentfulContentTypeRepository;
use Dothiv\Bundle\ContentfulBundle\Repository\ContentfulEntryRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListAssetsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('contentful:list:assets')
            ->setDescription('List local contentful assets');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ContentfulAssetRepositoryInterface $repo */
        /** @var $adapter \Dothiv\Bundle\ContentfulBundle\Adapter\ContentfulAssetAdapterInterface */
        /** @var TableHelper $table */
        $repo    = $this->getContainer()->get('dothiv_contentful.repo.asset');
        $adapter = $this->getContainer()->get('dothiv_contentful.asset');

        $assets     = array();
        $assetsSort = array();
        foreach ($repo->findAll() as $asset) {
            foreach ($asset->file as $locale => $file) {
                $localFile    = $adapter->getLocalFile($asset, $locale);
                $assets[]     = array(
                    $asset->getSpaceId(),
                    $asset->getId(),
                    $asset->getRevision(),
                    $locale,
                    $localFile->getPathname(),
                    $localFile->isFile() ? 'Yes' : 'No'
                );
                $assetsSort[] = $asset->getSpaceId();
            }
        }
        array_multisort($assetsSort, SORT_ASC, $assets);
        $table = $this->getHelperSet()->get('table');
        $table->setHeaders(array('Space', 'Asset', 'Revision', 'Locale', 'Local file', 'Cached'));
        $table->setRows($assets);
        $table->render($output);
    }

}
