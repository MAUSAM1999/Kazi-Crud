<?php

namespace YajTech\Crud\Interfaces;

interface RouteServiceInterface
{
    /**
     * Create routes for the specified model.
     *
     * @param string $name The name of the model.
     * @param string|null $module The module name, if applicable.
     * @param string $methods The HTTP methods for the routes.
     */
    public function createRoute(string $name, ?string $module, string $methods): string;

    /**
     * Check if a controller already exists.
     *
     * @param string $name
     * @param string|null $module
     * @return bool
     */
    public function routeExists(string $name, ?string $module): bool;
}
