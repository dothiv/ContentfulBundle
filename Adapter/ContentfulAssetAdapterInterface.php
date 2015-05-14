<?php

namespace Dothiv\Bundle\ContentfulBundle\Adapter;

use Dothiv\Bundle\ContentfulBundle\Item\ContentfulAsset;
use Psr\Log\LoggerAwareInterface;

interface ContentfulAssetAdapterInterface extends LoggerAwareInterface
{
    /**
     * @param ContentfulAsset $asset
     * @param string $locale
     *
     * @return string
     */
    function getRoute(ContentfulAsset $asset, $locale);

    /**
     * @param ContentfulAsset $asset
     * @param string $locale
     *
     * @return \SplFileInfo
     */
    function getLocalFile(ContentfulAsset $asset, $locale);

    /**
     * @param ContentfulAsset $asset
     *
     * @return void
     */
    function cache(ContentfulAsset $asset);
}
