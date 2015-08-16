<?php

namespace Dothiv\Bundle\ContentfulBundle\Controller;

use Dothiv\Bundle\ContentfulBundle\Cache\RequestLastModifiedCache;
use Dothiv\ValueObject\ClockValue;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This controller helps in sending the right cache headers based on the
 * contentful content displayed on the page
 */
class PageController
{

    /**
     * @var EngineInterface
     */
    private $renderer;

    /**
     * @var RequestLastModifiedCache
     */
    private $lastModifiedCache;

    /**
     * @var ClockValue
     */
    private $clock;

    /**
     * @var int
     */
    private $pageLifetime;

    /**
     * @param RequestLastModifiedCache $lastModifiedCache
     * @param EngineInterface          $renderer
     * @param ClockValue               $clock
     * @param int                      $pageLifetime In seconds
     */
    public function __construct(
        RequestLastModifiedCache $lastModifiedCache,
        EngineInterface $renderer,
        ClockValue $clock,
        $pageLifetime
    )
    {
        $this->lastModifiedCache = $lastModifiedCache;
        $this->renderer          = $renderer;
        $this->clock             = $clock;
        $this->pageLifetime      = (int)$pageLifetime;
    }

    /**
     * @param Request $request
     * @param string  $template
     *
     * @return Response
     */
    public function pageAction(Request $request, $template)
    {
        $response = new Response();
        $response->setPublic();
        $response->setSharedMaxAge($this->pageLifetime);
        $response->setExpires($this->clock->getNow()->modify(sprintf('+%d seconds', $this->pageLifetime)));

        $lmc = $this->getLastModifiedCache();

        // Check if page is not modified.
        $uriLastModified = $lmc->getLastModified($request);
        $response->setLastModified($uriLastModified);
        if ($response->isNotModified($request)) {
            return $response;
        }

        // Render page.
        $response = $this->renderer->renderResponse($template, array(), $response);

        // Store last modified.
        $lastModifiedDate = $lmc->getLastModifiedContent();
        $response->setLastModified($lastModifiedDate);
        $lmc->setLastModified($request, $lastModifiedDate);

        return $response;
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
     */
    protected function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * @return RequestLastModifiedCache
     */
    protected function getLastModifiedCache()
    {
        return $this->lastModifiedCache;
    }

    /**
     * @return ClockValue
     */
    protected function getClock()
    {
        return $this->clock;
    }
}
