<?php

namespace YajTech\Crud\Interfaces;

interface RequestServiceInterface
{

    /**
     * Create a request class for the specified request.
     *
     * @param string $name The name of the request.
     * @param string $type The type of request (e.g., CreateRequest, UpdateRequest).
     * @param array $fields The fields to include in the request.
     * @param string|null $module The module name, if applicable.
     * @return string The name of the created request.
     */
    public function createRequest(string $name, string $type, array $fields, ?string $module): string;

    /**
     * Check if a request class already exists.
     *
     * @param string $name The name of the request.
     * @param string|null $module The module name, if applicable.
     * @param string $type The type, if applicable.
     * @return bool True if the request exists, false otherwise.
     */
    public function requestExists(string $name, ?string $module, string $type): bool;
}
