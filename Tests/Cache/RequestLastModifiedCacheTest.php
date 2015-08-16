<?php


namespace Dothiv\Bundle\ContentfulBundle\Tests\Cache;

use Doctrine\Common\Cache\ArrayCache;
use Dothiv\Bundle\ContentfulBundle\Cache\RequestLastModifiedCache;
use Dothiv\Bundle\ContentfulBundle\Event\ContentfulViewEvent;
use Dothiv\Bundle\ContentfulBundle\Entity\Config;
use Dothiv\Bundle\ContentfulBundle\Repository\ConfigRepositoryInterface;

class RequestLastModifiedCacheTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ConfigRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockConfigRepo;

    /**
     * @test
     * @group BaseWebsiteBundle
     * @group Cache
     * @group Content
     * @group Cache
     */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceOf('\Dothiv\Bundle\ContentfulBundle\Cache\RequestLastModifiedCache', $this->createTestObject());
    }

    public function testDataProvider()
    {
        return array(
            array(new \DateTime('2013-12-31T12:34:56Z'), new \DateTime('2013-12-31T12:34:56Z'), new \DateTime('2012-01-02T12:34:56Z')),
            array(new \DateTime('2013-12-31T12:34:56Z'), new \DateTime('2014-01-02T12:34:56Z'), new \DateTime('2014-01-02T12:34:56Z'))
        );
    }

    /**
     * @test
     * @group        BaseWebsiteBundle
     * @group        Cache
     * @group        Content
     * @group        Cache
     * @depends      itShouldBeInstantiable
     *
     * @param \DateTime $contentModified
     * @param \DateTime $expectedModified
     * @param \DateTime $minLastModifiedDate
     *
     * @dataProvider testDataProvider
     */
    public function itShouldReturnLastModifiedContent(\DateTime $contentModified, \DateTime $expectedModified, \DateTime $minLastModifiedDate)
    {
        $this->mockConfigRepo->expects($this->once())->method('get')
            ->with(RequestLastModifiedCache::CONFIG_NAME)
            ->willReturnCallback(function () use ($minLastModifiedDate) {
                $config = new Config();
                $config->setName(RequestLastModifiedCache::CONFIG_NAME);
                $config->setValue($minLastModifiedDate->format(DATE_W3C));
                return $config;
            });

        $view         = new \stdClass();
        $view->cfMeta = array(
            'itemId'      => 'childItem',
            'updatedAt'   => $contentModified,
            'contentType' => 'Block'

        );
        $e            = new ContentfulViewEvent($view);
        $lmc          = $this->createTestObject();
        $lmc->onViewCreate($e);
        $this->assertEquals($expectedModified, $lmc->getLastModifiedContent());
    }

    /**
     * @return RequestLastModifiedCache
     */
    public function createTestObject()
    {
        $lmc = new RequestLastModifiedCache(new ArrayCache(), $this->mockConfigRepo);
        return $lmc;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->mockConfigRepo = $this->getMock('\Dothiv\Bundle\ContentfulBundle\Repository\ConfigRepositoryInterface');
    }
} 
