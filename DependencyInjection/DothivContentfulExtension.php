<?php

namespace Dothiv\ContentfulBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class DothivContentfulExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);
        $container->setParameter('dothiv_contentful.web_path', $config['web_path']);
        $container->setParameter('dothiv_contentful.local_path', $config['local_path']);
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('listener.yml');
        $loader->load('persistence.yml');
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $doctrineConfig = array();

        $doctrineConfig['orm']['mappings']['contentful_bundle_item'] = array(
            'type'   => 'annotation',
            'alias'  => 'ContentfulBundleItem',
            'dir'    => __DIR__ . '/../Item',
            'prefix' => 'Dothiv\ContentfulBundle\Item'
        );

        $doctrineConfig['orm']['mappings']['contentful_bundle_entity'] = array(
            'type'   => 'annotation',
            'alias'  => 'ContentfulBundleEntity',
            'dir'    => __DIR__ . '/../Entity',
            'prefix' => 'Dothiv\ContentfulBundle\Entity'
        );

        $container->prependExtensionConfig('doctrine', $doctrineConfig);
    }
}
