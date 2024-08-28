<?php

namespace YajTech\Crud\Interfaces;

interface ControllerServiceInterface
{
    /**
     * Create a controller for the specified model.
     *
     * @param string $name The name of the model.
     * @param string|null $module The module name, if applicable.
     * @return string The name of the created controller.
     */
    public function createController(string $name, ?string $module): string;

    /**
     * Check if a controller already exists.
     *
     * @param string $name
     * @param string|null $module
     * @return bool
     */
    public function controllerExists(string $name, ?string $module): bool;
}
