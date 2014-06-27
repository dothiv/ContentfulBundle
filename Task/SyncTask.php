<?php

namespace Dothiv\ContentfulBundle\Task;

use Dothiv\ContentfulBundle\Adapter\HttpClientAdapter;
use Dothiv\ContentfulBundle\Client\HttpClientInterface;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use Dothiv\ContentfulBundle\Repository\ContentfulStatusRepository;
use PhpOption\None;
use PhpOption\Option;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcher;


/**
 * This task synchronizes the latest changes from the contentful with the local database.
 */
class SyncTask
{
    use LoggerAwareTrait;

    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @var ContentfulStatusRepository
     */
    private $statusRepo;

    /**
     * @param HttpClientInterface        $client
     * @param EventDispatcher            $dispatcher
     * @param ContentfulStatusRepository $statusRepo
     */
    public function __construct(
        HttpClientInterface $client,
        EventDispatcher $dispatcher,
        ContentfulStatusRepository $statusRepo
    )
    {
        $this->client     = $client;
        $this->dispatcher = $dispatcher;
        $this->statusRepo = $statusRepo;
    }

    public function sync($spaceId, $accessToken)
    {
        $adapter = new HttpClientAdapter(
            $spaceId,
            $this->client,
            $this->dispatcher
        );
        $adapter->setLogger($this->logger);

        // Fetch status
        $status = $this->statusRepo->getBySpaceId($spaceId);
        $adapter->setNextSyncUrl($status->getNextSyncUrl());
        $this->client->setEtag($status->getETag());

        $nextSyncUrl = $adapter->sync();
        $status->setNextSyncUrl($nextSyncUrl);
        $status->setETag($this->client->header('etag'));
        $this->statusRepo->persist($status);
    }
}
