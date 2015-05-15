<?php

namespace Dothiv\Bundle\ContentfulBundle\Listener;

use Dothiv\Bundle\ContentfulBundle\Event\ContentfulAssetEvent;
use Dothiv\Bundle\ContentfulBundle\Item\ContentfulAsset;
use Dothiv\Bundle\ContentfulBundle\Item\Traits\ContentfulItem;
use Dothiv\Bundle\ContentfulBundle\Repository\ContentfulAssetRepositoryInterface;

class SyncAsset
{
    /**
     * @var ContentfulAssetRepositoryInterface
     */
    private $assetRepo;

    /**
     * @param ContentfulAssetRepositoryInterface $assetRepo
     */
    public function __construct(ContentfulAssetRepositoryInterface $assetRepo)
    {
        $this->assetRepo = $assetRepo;
    }

    /**
     * @param ContentfulAssetEvent $event
     */
    public function onAssetSync(ContentfulAssetEvent $event)
    {
        $syncAsset     = $event->getAsset();
        $assetOptional = $this->assetRepo->findNewestById($syncAsset->getSpaceId(), $syncAsset->getId());
        if ($assetOptional->isEmpty()) {
            $this->assetRepo->persist($syncAsset);
        } else {
            /** @var ContentfulAsset $existingAsset */
            $existingAsset = $assetOptional->get();
            if ($existingAsset->getRevision() < $syncAsset->getRevision()) {
                $this->assetRepo->persist($syncAsset);
            } else {
                $event->setAsset($existingAsset);
            }
        }
    }
}
