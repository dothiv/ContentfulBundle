<?php

spl_autoload_register(function ($class) {
    if (strpos($class, 'Dothiv\Bundle\ContentfulBundle') === 0) {
        $parts = explode('\\', $class);
        array_shift($parts);
        array_shift($parts);
        array_shift($parts);
        require_once __DIR__ . '/../' . join(DIRECTORY_SEPARATOR, $parts) . '.php';
        return true;
    }
    return false;
});
