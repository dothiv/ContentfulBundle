<?php

namespace Dothiv\Bundle\ContentfulBundle\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Dothiv\Bundle\ContentfulBundle\DothivContentfulBundleEvents;
use Dothiv\Bundle\ContentfulBundle\Event\ContentfulContentTypeEvent;
use Dothiv\Bundle\ContentfulBundle\Event\ContentfulContentTypesEvent;
use Dothiv\Bundle\ContentfulBundle\Item\ContentfulContentType;
use Dothiv\Bundle\ContentfulBundle\Item\Traits\ContentfulItem;
use Dothiv\Bundle\ContentfulBundle\Repository\ContentfulContentTypeRepository;
use Dothiv\Bundle\ContentfulBundle\Repository\ContentfulEntryRepository;

class SyncContentType
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
    public function onContentTypeSync(ContentfulContentTypeEvent $event)
    {
        $syncContentType     = $event->getContentType();
        $contentTypeOptional = $this->contentTypeRepo->findNewestById($syncContentType->getSpaceId(), $syncContentType->getId());
        if ($contentTypeOptional->isEmpty()) {
            $this->contentTypeRepo->persist($syncContentType);
        } else {
            /** @var ContentfulContentType $existingContentType */
            $existingContentType = $contentTypeOptional->get();
            if ($existingContentType->getRevision() < $syncContentType->getRevision()) {
                $this->contentTypeRepo->persist($syncContentType);
                if ($existingContentType->getDisplayField() != $syncContentType->getDisplayField()) {
                    // Update entries as the display field has changed.
                    foreach ($this->entryRepository->findByContentType($syncContentType) as $entry) {
                        $syncContentType->updateEntryName($entry);
                        $this->entryRepository->persist($entry);
                    }
                }
            } else {
                $event->setContentType($existingContentType);
            }
        }
    }

    /**
     * @param ContentfulContentTypesEvent $event
     */
    public function onContentTypesSync(ContentfulContentTypesEvent $event)
    {
        $contentTypesToSpaceId = new ArrayCollection();
        foreach ($event->getContentTypes() as $contentType) {
            if (!$contentTypesToSpaceId->containsKey($contentType->getSpaceId())) {
                $contentTypesToSpaceId->set($contentType->getSpaceId(), new ArrayCollection());
            }
            $contentTypesToSpaceId[$contentType->getSpaceId()][] = $contentType;
        }
        $idsMap = function (ContentfulContentType $type) {
            return $type->getId();
        };
        foreach ($contentTypesToSpaceId as $spaceId => $contentTypes) {
            /** @var ArrayCollection $contentTypes */
            $currentContentTypes = $this->contentTypeRepo->findAllBySpaceId($spaceId)->map($idsMap);
            $newContentTypes     = $contentTypes->map($idsMap);
            $old                 = $currentContentTypes->toArray();
            $new                 = $newContentTypes->toArray();
            foreach (array_diff($old, $new) as $deleted) {
                $event->getDispatcher()->dispatch(DothivContentfulBundleEvents::CONTENT_TYPE_DELETE, new ContentfulContentTypeEvent(
                    $this->contentTypeRepo->findNewestById($spaceId, $deleted)->get()
                ));
            }
        }
    }
}
