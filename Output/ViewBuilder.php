<?php

namespace Dothiv\Bundle\ContentfulBundle\Output;

use Dothiv\Bundle\ContentfulBundle\DothivContentfulBundleEvents;
use Dothiv\Bundle\ContentfulBundle\Event\ContentfulViewEvent;
use Dothiv\Bundle\ContentfulBundle\Adapter\ContentfulContentAdapter;
use Dothiv\Bundle\ContentfulBundle\Item\ContentfulAsset;
use Dothiv\Bundle\ContentfulBundle\Item\ContentfulEntry;
use Dothiv\Bundle\ContentfulBundle\Item\ContentfulItem;
use PhpOption\None;
use PhpOption\Option;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ViewBuilder
{
    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @var ContentfulContentAdapter
     */
    private $contentAdapter;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @param string                   $defaultLocale
     * @param ContentfulContentAdapter $contentAdapter
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct($defaultLocale, ContentfulContentAdapter $contentAdapter, EventDispatcherInterface $dispatcher)
    {
        $this->contentAdapter = $contentAdapter;
        $this->defaultLocale  = $defaultLocale;
        $this->dispatcher     = $dispatcher;
    }

    /**
     * @param ContentfulEntry $entry
     * @param string          $locale
     *
     * @return object
     */
    public function buildView(ContentfulEntry $entry, $locale)
    {
        $spaceId                         = $entry->getSpaceId();
        $fields                          = $this->localize($entry, $locale);
        $fields['cfMeta']                = array();
        $fields['cfMeta']['url']         = $entry->getContentfulUrl();
        $fields['cfMeta']['contentType'] = $this->contentAdapter->getContentTypeById($spaceId, $entry->getContentTypeId())->getName();
        $fields['cfMeta']['itemName']    = $entry->getName();
        $fields['cfMeta']['itemId']      = $entry->getId();
        $fields['cfMeta']['createdAt']   = $entry->getCreatedAt();
        $fields['cfMeta']['updatedAt']   = $entry->getUpdatedAt();
        $view                            = $this->createView($fields, $spaceId, $locale);
        return $view;
    }

    /**
     * @param ContentfulItem $entry
     * @param                $locale
     *
     * @return array
     */
    protected function localize(ContentfulItem $entry, $locale)
    {
        $fields = array();
        foreach ($entry->getFields() as $k => $v) {
            $localValue = isset($v[$locale]) ? $v[$locale] : null;
            if ($localValue === null) {
                if (isset($v[$this->defaultLocale])) {
                    $localValue = $v[$this->defaultLocale];
                }
            }
            if (is_null($localValue)) {
                continue;
            }
            if (is_scalar($localValue) && trim($localValue) === "") {
                continue;
            }
            $fields[$k] = $localValue;
        }
        return $fields;
    }

    /**
     * @param mixed  $value
     * @param string $spaceId
     * @param string $locale
     *
     * @return Option
     */
    protected function getValue($value, $spaceId, $locale)
    {
        if (is_scalar($value)) {
            return Option::fromValue($value);
        }
        if (is_object($value)) {
            // TODO: Generalize?
            if ($value instanceof \DateTime) {
                return Option::fromValue($value);
            }
        }
        if (is_array($value)) {
            if ($this->isLink($value)) {
                /** @var ContentfulItem $entry */
                $optionalEntry = $this->contentAdapter->findByTypeAndId($spaceId, $value['sys']['linkType'], $value['sys']['id']);
                if ($optionalEntry->isEmpty()) {
                    return None::create();
                }
                $entry = $optionalEntry->get();

                $fields                       = $this->localize($entry, $locale);
                $fields['cfMeta']             = array();
                $fields['cfMeta']['itemType'] = $value['sys']['linkType'];
                $fields['cfMeta']['itemId']   = $value['sys']['id'];
                $fields['cfMeta']['url']      = $entry->getContentfulUrl();
                if ($entry instanceof ContentfulEntry) {
                    /** @var ContentfulEntry $entry */
                    $fields['cfMeta']['contentType'] = $this->contentAdapter->getContentTypeById($spaceId, $entry->getContentTypeId())->getName();
                    $fields['cfMeta']['itemName']    = $entry->getName();
                    $fields['cfMeta']['createdAt']   = $entry->getCreatedAt();
                    $fields['cfMeta']['updatedAt']   = $entry->getUpdatedAt();
                }
                if ($entry instanceof ContentfulAsset) {
                    /** @var ContentfulAsset $entry */
                    $fields['cfMeta']['itemName']    = $fields['title'];
                    $fields['cfMeta']['contentType'] = 'Asset';
                    $fields['cfMeta']['createdAt']   = $entry->getCreatedAt();
                    $fields['cfMeta']['updatedAt']   = $entry->getUpdatedAt();
                }
                return Option::fromValue($this->createView($fields, $spaceId, $locale));
            } else {
                $newValue = array();
                foreach ($value as $k => $v) {
                    $vv = $this->getValue($v, $spaceId, $locale);
                    if ($vv->isDefined()) {
                        $newValue[$k] = $vv->get();
                    }

                }
                return Option::fromValue($newValue);
            }
        }
    }

    protected function createView(array $fields, $spaceId, $locale)
    {
        $view = new \stdClass();
        foreach ($fields as $k => $v) {
            $v = $this->getValue($v, $spaceId, $locale);
            if ($v->isDefined()) {
                $view->$k = $v->get();
            }
        }
        $view = $this->dispatcher->dispatch(DothivContentfulBundleEvents::CONTENTFUL_VIEW_CREATE, new ContentfulViewEvent($view))->getView();
        return $view;
    }

    protected function isLink(array $value)
    {
        if (!isset($value['sys'])) return false;
        $sys = $value['sys'];
        if (!isset($sys['type'])) return false;
        if ($sys['type'] != 'Link') return false;
        return true;
    }

    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->dispatcher;
    }
} 
