<?php

namespace YajTech\Crud\Helper;

use ReflectionException;

class GlobalHelper
{
    /**
     * @throws ReflectionException
     */
    public function constant_exists($class, $name): bool
    {
        if (is_object($class) || is_string($class)) {
            $reflect = new \ReflectionClass($class);
            return array_key_exists($name, $reflect->getConstants());
        }

        return false;
    }

    public static function generateFromStub(string $stubPath, array $replacements): string
    {
        // Load the stub file content
        $stub = file_get_contents($stubPath);

        // Replace the placeholders with actual values
        foreach ($replacements as $key => $value) {
            $stub = str_replace("{{ $key }}", $value, $stub);
        }

        return $stub;
    }
}
