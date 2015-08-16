<?php

namespace Dothiv\Bundle\ContentfulBundle\Cache;

use Doctrine\Common\Cache\Cache;
use Dothiv\Bundle\ContentfulBundle\Event\ContentfulViewEvent;
use Dothiv\Bundle\ContentfulBundle\Entity\Config;
use Dothiv\Bundle\ContentfulBundle\Repository\ConfigRepositoryInterface;
use Dothiv\Bundle\ContentfulBundle\Event\ContentfulEntryEvent;
use PhpOption\Option;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Stores modification times for requests based on the
 * update date of content items rendered on the page.
 */
class RequestLastModifiedCache
{
    use LoggerAwareTrait;

    const CONFIG_NAME = 'last_modified_content.min_last_modified';

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var \DateTime
     */
    private $lastModifiedContent;

    /**
     * @var Config
     */
    private $lastMinModified;

    /**
     * @var \DateTime
     */
    private $lastMinModifiedDate;

    /**
     * @var array
     */
    private $itemIds = array();

    /**
     * @var ConfigRepositoryInterface
     */
    private $configRepo;

    /**
     * @param Cache                     $cache
     * @param ConfigRepositoryInterface $configRepo
     */
    public function __construct(Cache $cache, ConfigRepositoryInterface $configRepo)
    {
        $this->cache      = $cache;
        $this->configRepo = $configRepo;
    }

    /**
     * Collect ViewEvents to build lastModified date.
     *
     * @param ContentfulViewEvent $e
     */
    public function onViewCreate(ContentfulViewEvent $e)
    {
        $viewMeta                           = $e->getView()->cfMeta;
        $updated                            = $viewMeta['updatedAt'];
        $this->itemIds[$viewMeta['itemId']] = true;
        if ($this->lastModifiedContent === null) {
            $this->lastModifiedContent = $updated;
        } else {
            if ($this->lastModifiedContent < $updated) {
                $this->lastModifiedContent = $updated;
            }
        }
    }

    /**
     * Returns the last modified date for a request.
     *
     * @param Request $request
     *
     * @return \DateTime
     */
    public function getLastModified(Request $request)
    {
        $minModified          = $this->getLastMinModifiedDate();
        $optionalLastModified = Option::fromValue($this->cache->fetch($this->getCacheKeyRequest(sha1($request->getUri()), 'lastmodified')), false);
        if ($optionalLastModified->isEmpty()) {
            return $minModified;
        }
        return max($minModified, new \DateTime($optionalLastModified->get()));
    }

    /**
     * Stores the last modified date for a request.
     *
     * @param Request   $request
     * @param \DateTime $lastModified
     */
    public function setLastModified(Request $request, \DateTime $lastModified)
    {
        $this->cache->save($this->getCacheKeyRequest(sha1($request->getUri()), 'lastmodified'), $lastModified->format('r'));

        foreach ($this->itemIds as $itemId => $bool) {
            $key                             = $this->getCacheKeyItem($itemId, 'uri');
            $urisForItem                     = Option::fromValue($this->cache->fetch($key), false)->getOrElse(array());
            $urisForItem[$request->getUri()] = $bool;
            $this->cache->save($key, $urisForItem);
            Option::fromValue($this->logger)->map(function (LoggerInterface $logger) use ($request, $itemId) {
                $logger->debug(sprintf('[ContentfulBundle:RequestLastModifiedCache] "%s" is used on "%s".', $itemId, $request->getUri()));
            });
        }
    }

    /**
     * @param string $uri
     * @param string $type
     *
     * @return string
     */
    protected function getCacheKeyRequest($uri, $type)
    {
        $cacheKey = 'dothiv_contentful-request_uri-' . $type . '-' . $uri;
        return $cacheKey;
    }

    /**
     * @param string $itemId
     * @param string $type
     *
     * @return string
     */
    protected function getCacheKeyItem($itemId, $type)
    {
        $cacheKey = 'dothiv_contentful-item_uri-' . $itemId . '-' . $type;
        return $cacheKey;
    }

    /**
     * Returns the last modified for the content that has been loaded in the current scope.
     *
     * @return \DateTime
     */
    public function getLastModifiedContent()
    {
        $lastMinModifiedDate = $this->getLastMinModifiedDate();
        return max($this->lastModifiedContent, $lastMinModifiedDate);
    }

    /**
     * This method gets called when an Entry is update and updates the
     * last modified time cache entry for every page it is used.
     *
     * There is a problem with this implementation: adding _new_ child elements
     * is not picked up because they were never used before.
     *
     * @param ContentfulEntryEvent $e
     */
    public function onEntryUpdate(ContentfulEntryEvent $e)
    {
        $entry             = $e->getEntry();
        $key               = $this->getCacheKeyItem($entry->getId(), 'uri');
        $urisForItemOption = Option::fromValue($this->cache->fetch($key), false);
        if ($urisForItemOption->isEmpty()) {
            Option::fromValue($this->logger)->map(function (LoggerInterface $logger) use ($entry) {
                $logger->debug(sprintf('[ContentfulBundle:RequestLastModifiedCache] Entry "%s" is not used.', $entry->getId()));
            });
            return;
        }
        // Update
        $urisForItem = $urisForItemOption->get();
        foreach ($urisForItem as $uri => $bool) {
            $key          = $this->getCacheKeyRequest(sha1($uri), 'lastmodified');
            $lastModified = $this->cache->fetch($key);
            if ($lastModified >= $entry->getUpdatedAt()->format('r')) {
                Option::fromValue($this->logger)->map(function (LoggerInterface $logger) use ($lastModified, $uri) {
                    $logger->debug(sprintf('[ContentfulBundle:RequestLastModifiedCache] "%s" was last modified at "%s". Entry is older.', $uri, $lastModified));
                });
                continue;
            }
            $this->cache->save($key, $entry->getUpdatedAt()->format('r'));
            Option::fromValue($this->logger)->map(function (LoggerInterface $logger) use ($entry, $uri) {
                $logger->debug(sprintf('[ContentfulBundle:RequestLastModifiedCache] Setting last modified time for "%s" to "%s".', $uri, $entry->getUpdatedAt()->format('r')));
            });
        }
    }

    /**
     * @return \DateTime
     */
    protected function getLastMinModifiedDate()
    {
        if ($this->lastMinModified === null) {
            $this->lastMinModified     = $this->configRepo->get(static::CONFIG_NAME);
            $this->lastMinModifiedDate = new \DateTime($this->lastMinModified->getValue());
        }
        return $this->lastMinModifiedDate;
    }
} 
