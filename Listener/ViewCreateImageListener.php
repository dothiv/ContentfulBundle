<?php

namespace Dothiv\Bundle\ContentfulBundle\Listener;

use Dothiv\Bundle\ContentfulBundle\Event\ContentfulViewEvent;
use Dothiv\Bundle\ContentfulBundle\Output\ImageAssetScaler;

class ViewCreateImageListener
{
    /**
     * @var ImageAssetScaler
     */
    private $scaler;

    /**
     * @var string
     */
    private $contentType;

    /**
     * @var string
     */
    private $field;

    /**
     * @param ImageAssetScaler $scaler
     * @param string           $contentType
     * @param string           $field
     */
    public function __construct(ImageAssetScaler $scaler, $contentType, $field)
    {
        $this->scaler      = $scaler;
        $this->contentType = $contentType;
        $this->field       = $field;
    }

    /**
     * @param ContentfulViewEvent $event
     */
    public function onViewCreate(ContentfulViewEvent $event)
    {
        $view = $event->getView();
        if ($view->cfMeta['contentType'] != $this->contentType) return;
        $field = $this->field;
        if (!property_exists($view, $field)) return;
        if (is_array($view->$field)) {
            foreach ($view->$field as $k => $v) {
                $view->{$field}[$k]->file['thumbnails'] = $this->generateThumbnailUrls($v->file['url']);
            }
        } else {
            $view->$field->file['thumbnails'] = $this->generateThumbnailUrls($view->$field->file['url']);
        }
        $event->setView($view);
    }

    protected function generateThumbnailUrls($url)
    {
        $thumbnails = array();
        foreach ($this->scaler->getSizes() as $size) {
            $thumbnails[$size->getLabel()] = $this->scaler->getScaledUrl($url, $size);
        }
        return $thumbnails;
    }
}
