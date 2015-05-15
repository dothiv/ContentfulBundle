<?php

namespace Dothiv\Bundle\ContentfulBundle\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Dothiv\Bundle\ContentfulBundle\DothivContentfulBundleEvents;
use Dothiv\Bundle\ContentfulBundle\Event\ContentfulContentTypeEvent;
use Dothiv\Bundle\ContentfulBundle\Event\ContentfulContentTypesEvent;
use Dothiv\Bundle\ContentfulBundle\Event\ContentfulEntryEvent;
use Dothiv\Bundle\ContentfulBundle\Event\DeletedContentfulEntryEvent;
use Dothiv\Bundle\ContentfulBundle\Item\ContentfulContentType;
use Dothiv\Bundle\ContentfulBundle\Item\Traits\ContentfulItem;
use Dothiv\Bundle\ContentfulBundle\Repository\ContentfulContentTypeRepository;
use Dothiv\Bundle\ContentfulBundle\Repository\ContentfulEntryRepository;

class DeleteEntry
{
    /**
     * @var ContentfulEntryRepository
     */
    private $entryRepository;

    /**
     * @param ContentfulEntryRepository $entryRepository
     */
    public function __construct(ContentfulEntryRepository $entryRepository)
    {
        $this->entryRepository = $entryRepository;
    }

    /**
     * @param DeletedContentfulEntryEvent $event
     */
    public function onEntryDelete(DeletedContentfulEntryEvent $event)
    {
        $optionalEntry = $this->entryRepository->findNewestById($event->getEntry()->getSpaceId(), $event->getEntry()->getId());
        if ($optionalEntry->isEmpty()) {
            return;
        }
        $this->entryRepository->remove($optionalEntry->get());
    }
}
