<?php

namespace Dothiv\ContentfulBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Dothiv\ContentfulBundle\Entity\Status;
use Dothiv\ContentfulBundle\Item\ContentfulAsset;
use PhpOption\Option;

class DoctrineContentfulStatusRepository extends EntityRepository implements ContentfulStatusRepository
{
    /**
     * {@inheritdoc}
     */
    function getBySpaceId($spaceId)
    {
        $result = $this->findBy(array('spaceId' => $spaceId), null, 1);

        return Option::fromValue(count($result) == 1 ? array_shift($result) : null)->getOrCall(function () use ($spaceId) {
            $status = new Status();
            $status->setSpaceId($spaceId);
            return $status;
        });
    }
}
