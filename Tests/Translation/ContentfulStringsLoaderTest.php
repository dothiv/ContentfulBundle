<?php

namespace Dothiv\Bundle\ContentfulBundle\Tests\Translation;

use Dothiv\Bundle\ContentfulBundle\Item\ContentfulEntry;
use Dothiv\Bundle\ContentfulBundle\Output\Content;
use Dothiv\Bundle\ContentfulBundle\Translation\ContentfulStringsLoader;

class ContentfulStringsLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Content
     */
    private $mockContent;

    /**
     * @test
     * @group BaseWebsiteBundle
     */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceOf('\Dothiv\Bundle\ContentfulBundle\Translation\ContentfulStringsLoader', $this->getTestObject());
    }

    /**
     * @return array
     */
    public function getEntries()
    {
        $s1        = new ContentfulEntry();
        $s1->code  = 'string1';
        $s1->value = 'value1';
        $s2        = new ContentfulEntry();
        $s2->code  = 'string2';
        $s2->value = 'value2';
        $s3        = new ContentfulEntry();
        $s3->code  = 'string3';
        return array(array(array($s1, $s2, $s3)));
    }

    /**
     * @test
     * @group        BaseWebsiteBundle
     * @depends      itShouldBeInstantiable
     * @dataProvider getEntries
     *
     * @param array $entries Test entries
     */
    public function itShouldLoadStrings(array $entries)
    {
        $this->mockContent->expects($this->once())
            ->method('buildEntries')
            ->with('String', 'de')
            ->will($this->returnValue($entries));

        $loader    = $this->getTestObject();
        $catalogue = $loader->load(null, 'de', 'somedomain');
        $strings   = $catalogue->all();
        $this->assertEquals(3, count($strings['somedomain']));
        $this->assertEquals('value1', $strings['somedomain']['string1']);
        $this->assertEquals('value2', $strings['somedomain']['string2']);
        $this->assertEquals('', $strings['somedomain']['string3']);
    }

    /**
     * @test
     * @group        BaseWebsiteBundle
     * @depends      itShouldLoadStrings
     * @dataProvider getEntries
     *
     * @param array $entries Test entries
     */
    public function itShouldLoadKeysAsStrings(array $entries)
    {
        $this->mockContent->expects($this->once())
            ->method('buildEntries')
            ->with('String', 'ky')
            ->will($this->returnValue($entries));

        $loader    = $this->getTestObject();
        $catalogue = $loader->load(null, 'ky', 'somedomain');
        $strings   = $catalogue->all();
        $this->assertEquals('string1', $strings['somedomain']['string1']);
        $this->assertEquals('string2', $strings['somedomain']['string2']);
        $this->assertEquals('string3', $strings['somedomain']['string3']);
    }

    protected function getTestObject()
    {
        return new ContentfulStringsLoader($this->mockContent, 'String', 'ky');
    }

    public function setUp()
    {
        $this->mockContent = $this->getMockBuilder('\Dothiv\Bundle\ContentfulBundle\Output\Content')
            ->disableOriginalConstructor()
            ->getMock();
    }
} 
