<?php

namespace Dothiv\ContentfulBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Dothiv\ContentfulBundle\Entity\Status;
use Dothiv\ContentfulBundle\Item\ContentfulAsset;
use PhpOption\Option;

interface ContentfulStatusRepository
{
    /**
     * @param string $spaceId
     *
     * @return Status
     */
    function getBySpaceId($spaceId);


    /**
     * @param Status $entry
     *
     * @return void
     */
    function persist(Status $entry);
}
