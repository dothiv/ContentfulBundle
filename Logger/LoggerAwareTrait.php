<?php

namespace Dothiv\ContentfulBundle\Logger;

use PhpOption\Option;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

trait LoggerAwareTrait
{
    use \Psr\Log\LoggerAwareTrait;

    protected function log()
    {
        $args = func_get_args();
        Option::fromValue($this->logger)->map(function (LoggerInterface $logger) use ($args) {
            $logger->debug(call_user_func_array('sprintf', $args));
        });
    }
} 
