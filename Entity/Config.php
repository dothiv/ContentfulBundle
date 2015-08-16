<?php

namespace Dothiv\Bundle\ContentfulBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints as AssertORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Represents a config setting.
 *
 * @ORM\Entity(repositoryClass="Dothiv\Bundle\ContentfulBundle\Repository\DoctrineConfigRepository")
 * @ORM\Table(name="ContentfulConfig")
 * @Serializer\ExclusionPolicy("all")
 */
class Config
{
    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @param \DateTime $created
     */
    public function setCreated(\DateTime $created)
    {
        $this->created = $created;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @param \DateTime $updated
     */
    public function setUpdated(\DateTime $updated)
    {
        $this->updated = $updated;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Name of the config setting.
     *
     * @ORM\Id
     * @ORM\Column(type="string",length=255,nullable=false)
     * @Assert\NotBlank
     * @Assert\NotNull
     * @Assert\Length(max=255)
     * @var string
     */
    protected $name;

    /**
     * Value of the config setting.
     *
     * @ORM\Column(type="text",nullable=false)
     * @Assert\NotBlank
     * @Assert\NotNull
     * @var string
     */
    protected $value;

    /**
     * @param string $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicId()
    {
        return $this->getId();
    }
} 
