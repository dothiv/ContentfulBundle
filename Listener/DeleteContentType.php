<?php

namespace Dothiv\Bundle\ContentfulBundle\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Dothiv\Bundle\ContentfulBundle\ContentfulEvents;
use Dothiv\Bundle\ContentfulBundle\Event\ContentfulContentTypeEvent;
use Dothiv\Bundle\ContentfulBundle\Event\ContentfulContentTypesEvent;
use Dothiv\Bundle\ContentfulBundle\Event\ContentfulEntryEvent;
use Dothiv\Bundle\ContentfulBundle\Event\DeletedContentfulEntryEvent;
use Dothiv\Bundle\ContentfulBundle\Item\ContentfulContentType;
use Dothiv\Bundle\ContentfulBundle\Item\DeletedContentfulEntry;
use Dothiv\Bundle\ContentfulBundle\Item\Traits\ContentfulItem;
use Dothiv\Bundle\ContentfulBundle\Repository\ContentfulContentTypeRepository;
use Dothiv\Bundle\ContentfulBundle\Repository\ContentfulEntryRepository;

class DeleteContentType
{
    /**
     * @var ContentfulContentTypeRepository
     */
    private $contentTypeRepo;

    /**
     * @var ContentfulEntryRepository
     */
    private $entryRepository;

    /**
     * @param ContentfulContentTypeRepository $contentTypeRepo
     * @param ContentfulEntryRepository       $entryRepository
     */
    public function __construct(ContentfulContentTypeRepository $contentTypeRepo, ContentfulEntryRepository $entryRepository)
    {
        $this->contentTypeRepo = $contentTypeRepo;
        $this->entryRepository = $entryRepository;
    }

    /**
     * @param ContentfulContentTypeEvent $event
     */
    public function onContentTypeDelete(ContentfulContentTypeEvent $event)
    {
        $contentType = $event->getContentType();
        foreach ($this->entryRepository->findByContentType($contentType) as $entry) {
            $deletedEntry = DeletedContentfulEntry::fromEntry($entry);
            $event->getDispatcher()->dispatch(ContentfulEvents::ENTRY_DELETE, new DeletedContentfulEntryEvent($deletedEntry));
        }
        $this->contentTypeRepo->remove($contentType);
    }
}
