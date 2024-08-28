<?php

namespace YajTech\Crud\Interfaces;

interface ResourceServiceInterface
{
    /**
     * Create a resource class for the specified model.
     *
     * @param string $name The name of the model.
     * @param string $type The type of resource (e.g., Resource, Collection).
     * @param array $fields The fields to include in the resource.
     * @param string|null $module The module name, if applicable.
     * @return string The name of the created resource.
     */
    public function createResource(string $name, string $type, array $fields, ?string $module): string;

    /**
     * Check if a resource class already exists.
     *
     * @param string $name The name of the resource.
     * @param string|null $module The module name, if applicable.
     * @param string $type The type, if applicable.
     * @return bool True if the resource exists, false otherwise.
     */
    public function resourceExists(string $name, ?string $module, string $type): bool;
}
