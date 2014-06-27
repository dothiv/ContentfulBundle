<?php

namespace Dothiv\ContentfulBundle\Tests;

use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader as DataFixturesLoader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\FixtureInterface;

abstract class EntityManagerAwareTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getTestEntityManager()
    {
        return $this->getTestContainer()->get('doctrine.orm.entity_manager');
    }
}
