<?php

namespace Dothiv\Bundle\ContentfulBundle\Event;

use Dothiv\Bundle\ContentfulBundle\Item\ContentfulEntry;
use Symfony\Component\EventDispatcher\Event;

class ContentfulEntryEvent extends Event
{
    /**
     * @var ContentfulEntry
     */
    private $entry;

    /**
     * @param ContentfulEntry $entry
     */
    public function __construct(ContentfulEntry $entry)
    {
        $this->entry = $entry;
    }

    /**
     * @return ContentfulEntry
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @param ContentfulEntry $entry
     */
    public function setEntry($entry)
    {
        $this->entry = $entry;
    }
}

