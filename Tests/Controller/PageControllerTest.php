<?php

namespace Dothiv\Bundle\ContentfulBundle\Tests\Controller;

use Doctrine\Common\Cache\ArrayCache;
use Dothiv\Bundle\ContentfulBundle\DothivContentfulBundleEvents;
use Dothiv\Bundle\ContentfulBundle\Cache\RequestLastModifiedCache;
use Dothiv\Bundle\ContentfulBundle\Output\Content;
use Dothiv\Bundle\ContentfulBundle\Output\ViewBuilder;
use Dothiv\Bundle\ContentfulBundle\Controller\PageController;
use Dothiv\Bundle\ContentfulBundle\Event\ContentfulViewEvent;
use Dothiv\Bundle\ContentfulBundle\Entity\Config;
use Dothiv\Bundle\ContentfulBundle\Repository\ConfigRepositoryInterface;
use Dothiv\ValueObject\ClockValue;
use Dothiv\Bundle\ContentfulBundle\Event\ContentfulEntryEvent;
use Dothiv\Bundle\ContentfulBundle\Item\ContentfulEntry;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PageControllerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ConfigRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockConfigRepo;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Content
     */
    private $mockContent;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ViewBuilder
     */
    private $mockViewBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
     */
    private $mockRenderer;

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @test
     * @group BaseWebsiteBundle
     * @group Controller
     */
    public function itShouldBeInstantiateable()
    {
        $controller = $this->createTestObject();
        $this->assertInstanceOf('\Dothiv\Bundle\ContentfulBundle\Controller\PageController', $controller);
    }

    /**
     * @test
     * @group   BaseWebsiteBundle
     * @group   Controller
     * @depends itShouldBeInstantiateable
     */
    public function itShouldSendLastModifiedHeader()
    {
        $controller = $this->createTestObject();
        $this->expectMinLastModifiedDate(new \DateTime('2014-01-01T12:34:56Z'));

        $dateItem1 = new \DateTime('2014-06-02T08:06:17Z');
        $dateItem2 = new \DateTime('2014-06-03T08:06:17Z');

        $item1         = new \stdClass();
        $item1->cfMeta = array(
            'itemId'      => 'olderItem',
            'updatedAt'   => $dateItem1,
            'contentType' => 'Block'
        );
        $item2         = new \stdClass();
        $item2->cfMeta = array(
            'itemId'      => 'newerItem',
            'updatedAt'   => $dateItem2,
            'contentType' => 'Block'

        );

        // Update
        $dateItem1Updated = new \DateTime('2014-06-04T08:06:17Z');
        $item1update      = new ContentfulEntry();
        $item1update->setId('olderItem');
        $item1update->setUpdatedAt($dateItem1Updated);
        $item1updated         = new \stdClass();
        $item1updated->cfMeta = array(
            'itemId'      => 'olderItem',
            'updatedAt'   => $dateItem1Updated,
            'contentType' => 'Block'
        );

        // It should build the view for the page.
        $dispatcher = $this->dispatcher;
        // At first, $item1 is older than item2
        $this->mockRenderer->expects($this->at(0))->method('renderResponse')
            ->willReturnCallback(function ($template, $data, Response $response) use ($dispatcher, $item1, $item2) {
                $this->assertEquals('test', $template);
                $this->assertEquals(array(), $data);
                $dispatcher->dispatch(DothivContentfulBundleEvents::CONTENTFUL_VIEW_CREATE, new ContentfulViewEvent($item1));
                $dispatcher->dispatch(DothivContentfulBundleEvents::CONTENTFUL_VIEW_CREATE, new ContentfulViewEvent($item2));
                return $response;
            });

        // later item1 is newer than item2
        $this->mockRenderer->expects($this->at(1))->method('renderResponse')
            ->willReturnCallback(function ($template, $data, Response $response) use ($dispatcher, $item1updated, $item2) {
                $this->assertEquals('test', $template);
                $this->assertEquals(array(), $data);
                $dispatcher->dispatch(DothivContentfulBundleEvents::CONTENTFUL_VIEW_CREATE, new ContentfulViewEvent($item1updated));
                $dispatcher->dispatch(DothivContentfulBundleEvents::CONTENTFUL_VIEW_CREATE, new ContentfulViewEvent($item2));
                return $response;
            });

        // Get uncached Response
        $request  = new Request();
        $response = $controller->pageAction(
            $request,
            'test'
        );

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(200, $response->getStatusCode(), 'Request without If-Modified-Since should return status 200!');
        $this->assertTrue($response->headers->hasCacheControlDirective('public'), 'It should be public!');
        $this->assertTrue($response->isCacheable(), 'It should be cacheable!');
        $this->assertEquals($dateItem2, $response->getLastModified(), 'The last modified date should be that of the newest entry!');

        // Get cached response
        $request = new Request();
        $dateItem2->setTimezone(new \DateTimeZone('UTC'));
        $request->headers->add(array('If-Modified-Since' => $dateItem2->format('D, d M Y H:i:s') . ' GMT'));
        $response = $controller->pageAction(
            $request,
            'test'
        );
        $this->assertEquals(304, $response->getStatusCode(), 'Request with If-Modified-Since should return status 304!');

        // Update the content
        $this->dispatcher->dispatch(DothivContentfulBundleEvents::ENTRY_SYNC, new ContentfulEntryEvent($item1update));

        // Content update should return uncached response
        $request  = new Request();
        $response = $controller->pageAction(
            $request,
            'test'
        );
        $this->assertEquals(200, $response->getStatusCode(), 'After update it should return a new version.');
        $this->assertEquals($dateItem1Updated, $response->getLastModified(), 'The last modified date should be that of the updated entry!');
    }

    /**
     * @test
     * @group   BaseWebsiteBundle
     * @group   Controller
     * @depends itShouldSendLastModifiedHeader
     */
    public function itShouldSendExpiresHeader()
    {
        $this->expectMinLastModifiedDate(null);
        $this->mockRenderer->expects($this->any())->method('renderResponse')
            ->willReturnCallback(function ($template, $data, Response $response) {
                return $response;
            });
        // Get uncached response
        $controller = $this->createTestObject();
        $request    = new Request();
        $response   = $controller->pageAction(
            $request,
            'test'
        );
        $this->assertEquals(
            $this->getClock()->getNow()->modify('+30 minutes'),
            $response->getExpires()
        );
    }

    /**
     * @return PageController
     */
    protected function createTestObject()
    {
        $lmc = new RequestLastModifiedCache(new ArrayCache(), $this->mockConfigRepo);

        $this->dispatcher->addListener(
            DothivContentfulBundleEvents::CONTENTFUL_VIEW_CREATE, array($lmc, 'onViewCreate')
        );
        $this->dispatcher->addListener(
            DothivContentfulBundleEvents::ENTRY_SYNC, array($lmc, 'onEntryUpdate')
        );
        return new PageController(
            $lmc,
            $this->mockRenderer,
            $this->getClock(),
            1800
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->mockViewBuilder = $this->getMockBuilder('\Dothiv\Bundle\ContentfulBundle\Output\ViewBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dispatcher      = new EventDispatcher();

        $this->mockContent = $this->getMockBuilder('\Dothiv\Bundle\ContentfulBundle\Output\Content')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockRenderer = $this->getMock('\Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $this->mockConfigRepo = $this->getMock('\Dothiv\Bundle\ContentfulBundle\Repository\ConfigRepositoryInterface');
    }

    /**
     * @return ClockValue
     */
    protected function getClock()
    {
        $clock = new ClockValue(new \DateTime('2014-08-01T12:34:56Z'));
        return $clock;
    }

    /**
     * @param \DateTime $minLastModifiedDate
     */
    protected function expectMinLastModifiedDate(\DateTime $minLastModifiedDate = null)
    {
        $this->mockConfigRepo->expects($this->once())->method('get')
            ->with(RequestLastModifiedCache::CONFIG_NAME)
            ->willReturnCallback(function () use ($minLastModifiedDate) {
                $config = new Config();
                $config->setName(RequestLastModifiedCache::CONFIG_NAME);
                if ($minLastModifiedDate !== null) {
                    $config->setValue($minLastModifiedDate->format(DATE_W3C));
                }
                return $config;
            });
    }

}
