<?php

namespace Dothiv\Bundle\ContentfulBundle\Adapter;

use Psr\Log\LoggerAwareInterface;

interface ContentfulApiAdapter extends LoggerAwareInterface
{
    function sync();
}
