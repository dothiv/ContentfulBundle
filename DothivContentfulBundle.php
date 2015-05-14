<?php

namespace Dothiv\Bundle\ContentfulBundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\DependencyInjection\RegisterListenersPass;

class DothivContentfulBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(
            new RegisterListenersPass(
                'dothiv_contentful.event_dispatcher',
                'dothiv_contentful.event_listener',
                'dothiv_contentful.event_subscriber'
            ),
            PassConfig::TYPE_BEFORE_REMOVING
        );
    }
}
