<?php

namespace Dothiv\Bundle\ContentfulBundle\Output;

use Dothiv\Bundle\ContentfulBundle\Adapter\ContentfulContentAdapter;
use Dothiv\Bundle\ContentfulBundle\Exception\InvalidArgumentException;
use Dothiv\Bundle\ContentfulBundle\Item\ContentfulEntry;

class Content implements ContentInterface
{

    /**
     * @var ContentfulContentAdapter
     */
    private $contentAdapter;

    /**
     * @var ViewBuilder
     */
    private $viewBuilder;

    /**
     * @var string
     */
    private $spaceId;

    /**
     * @param ContentfulContentAdapter $contentAdapter
     * @param ViewBuilder              $viewBuilder
     * @param string                   $spaceId
     */
    public function __construct(ContentfulContentAdapter $contentAdapter, ViewBuilder $viewBuilder, $spaceId)
    {
        $this->contentAdapter = $contentAdapter;
        $this->viewBuilder    = $viewBuilder;
        $this->spaceId        = $spaceId;
    }

    /**
     * {@inheritdoc}
     */
    public function buildEntry($contentTypeName, $entryName, $locale)
    {
        /** @var ContentfulEntry $entry */
        $entry = $this->contentAdapter->findByContentTypeNameAndEntryName($this->spaceId, $contentTypeName, $entryName)->getOrCall(
            function () use ($contentTypeName, $entryName) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Failed to find entry named "%s" with content type "%s" in space "%s"!',
                        $entryName,
                        $contentTypeName,
                        $this->spaceId
                    )
                );
            }
        );
        return $this->viewBuilder->buildView($entry, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function buildEntries($contentTypeName, $locale)
    {
        /** @var ContentfulEntry $entry */
        $entries = $this->contentAdapter->findByContentTypeName($this->spaceId, $contentTypeName);
        $view    = array();
        foreach ($entries as $entry) {
            $view[] = $this->viewBuilder->buildView($entry, $locale);
        }
        return $view;
    }

    /**
     * @return ViewBuilder
     */
    public function getViewBuilder()
    {
        return $this->viewBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getSpaceId()
    {
        return $this->spaceId;
    }
}
