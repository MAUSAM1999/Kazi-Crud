<?php

namespace YajTech\Crud\Interfaces;

interface ModelServiceInterface
{
    /**
     * Create a model with the specified fields.
     *
     * @param string $name The name of the model.
     * @param array $fields The fields to include in the model.
     * @param string|null $module The module name, if applicable.
     * @return string The name of the created model.
     */
    public function createModel(string $name, array $fields, ?string $module): string;

    /**
     * Check if a model already exists.
     *
     * @param string $name The name of the model.
     * @param string|null $module The module name, if applicable.
     * @return bool True if the model exists, false otherwise.
     */
    public function modelExists(string $name, ?string $module): bool;
}
