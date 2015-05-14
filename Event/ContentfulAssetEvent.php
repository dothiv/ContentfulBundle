<?php

namespace Dothiv\Bundle\ContentfulBundle\Event;

use Dothiv\Bundle\ContentfulBundle\Item\ContentfulAsset;
use Symfony\Component\EventDispatcher\Event;

class ContentfulAssetEvent extends Event
{
    /**
     * @var ContentfulAsset
     */
    private $asset;

    /**
     * @param ContentfulAsset $asset
     */
    public function __construct(ContentfulAsset $asset)
    {
        $this->asset = $asset;
    }

    /**
     * @return ContentfulAsset
     */
    public function getAsset()
    {
        return $this->asset;
    }

    /**
     * @param ContentfulAsset $asset
     */
    public function setAsset($asset)
    {
        $this->asset = $asset;
    }
}
