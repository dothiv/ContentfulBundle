<?php

namespace Dothiv\Bundle\ContentfulBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContentfulThumbnailsCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $imageScaler = $container->getDefinition('dothiv_contentful.image_scaler.image_scaler');
        foreach ($container->getParameter('dothiv_contentful.thumbnails') as $label => $thumbnailConfig) {
            $imageScaler->addMethodCall(
                'addSize',
                array(
                    $label,
                    $thumbnailConfig['width'],
                    $thumbnailConfig['height'],
                    $thumbnailConfig['thumbnail'],
                    $thumbnailConfig['exact'],
                    $thumbnailConfig['fillbg']
                )
            );
        }
    }
}
