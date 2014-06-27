<?php

namespace Dothiv\ContentfulBundle\Tests\Functional\Command;

use Doctrine\ORM\EntityManager;
use Dothiv\ContentfulBundle\Command\SyncCommand;
use Dothiv\ContentfulBundle\Task\SyncTask;
use Dothiv\ContentfulBundle\Tests\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SyncCommandTest extends TestCase
{
    /**
     * @var InputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockInput;

    /**
     * @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockOutput;

    /**
     * @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockContainer;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockEntityManager;

    /**
     * @var SyncTask|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSyncTask;

    /**
     * @test
     * @group ContentfulBundle
     * @group ContentfulBundle/Sync
     */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceOf('\Dothiv\ContentfulBundle\Command\SyncCommand', $this->getTestObject());
    }

    /**
     * @test
     * @group   ContentfulBundle
     * @group   ContentfulBundle/Sync
     * @depends itShouldBeInstantiable
     */
    public function itShouldSync()
    {
        // Prepare.
        $this->mockContainer->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap(array(
                array('doctrine.orm.entity_manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->mockEntityManager),
                array('dothiv_contentful.task.sync', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->mockSyncTask),
            )));

        $spaceId     = 1234;
        $accessToken = 'abcd';

        $this->mockInput
            ->expects($this->any())
            ->method('getOption')
            ->will($this->returnValueMap(array(
                array('space', $spaceId),
                array('access_token', $accessToken),
            )));

        $this->mockSyncTask
            ->expects($this->once())
            ->method('sync')
            ->with($spaceId, $accessToken);

        // Run.
        $command = $this->getTestObject();
        $command->run(
            $this->mockInput,
            $this->mockOutput
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->mockInput         = $this->getMockBuilder('\Symfony\Component\Console\Input\InputInterface')->getMock();
        $this->mockOutput        = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')->getMock();
        $this->mockContainer     = $this->getMockBuilder('\Symfony\Component\DependencyInjection\ContainerInterface')->getMock();
        $this->mockEntityManager = $this->getMockBuilder('\Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $this->mockSyncTask      = $this->getMockBuilder('\Dothiv\ContentfulBundle\Task\SyncTask')->disableOriginalConstructor()->getMock();
    }

    /**
     * @return SyncCommand
     */
    protected function getTestObject()
    {
        $command = new SyncCommand();
        $command->setContainer($this->mockContainer);
        return $command;
    }
}
