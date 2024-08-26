<?php

namespace YajTech\Crud\Helper;

class GlobalHelper
{
    /**
     * @throws \ReflectionException
     */
    public function constant_exists($class, $name): bool
    {
        if (is_object($class) || is_string($class)) {
            $reflect = new \ReflectionClass($class);
            return array_key_exists($name, $reflect->getConstants());
        }

        return false;
    }
}
