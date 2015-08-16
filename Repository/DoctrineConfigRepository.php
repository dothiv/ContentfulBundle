<?php


namespace Dothiv\BusinessBundle\Repository;

use Doctrine\ORM\EntityRepository as DoctrineEntityRepository;
use Dothiv\Bundle\ContentfulBundle\Entity\Config;
use Dothiv\Bundle\ContentfulBundle\Exception\InvalidArgumentException;
use Dothiv\Bundle\ContentfulBundle\Repository\ConfigRepositoryInterface;
use PhpOption\Option;
use Symfony\Component\Validator\ValidatorInterface;

class DoctrineConfigRepository extends DoctrineEntityRepository implements ConfigRepositoryInterface
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @param object     $entity
     * @param array|null $groups The validation groups to validate.
     *
     * @throws InvalidArgumentException if $entity is invalid
     * @return object $entity
     */
    protected function validate($entity, array $groups = null)
    {
        $errors = $this->validator->validate($entity, $groups);
        if (count($errors) != 0) {
            throw new InvalidArgumentException((string)$errors);
        }
        return $entity;
    }

    /**
     * @param ValidatorInterface $validator
     */
    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function persist(Config $config)
    {
        $this->getEntityManager()->persist($this->validate($config));
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->getEntityManager()->flush();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    function get($key)
    {
        return Option::fromValue($this->findOneBy(
            array('name' => $key)
        ))->getOrCall(function () use ($key) {
            $config = new Config();
            $config->setName($key);
            return $config;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getItemByIdentifier($identifier)
    {
        return Option::fromValue($this->get($identifier));
    }
}
