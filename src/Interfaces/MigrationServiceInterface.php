<?php

namespace YajTech\Crud\Interfaces;

interface MigrationServiceInterface
{
    /**
     * Create a migration for the specified model.
     *
     * @param string $pluralModel The pluralized name of the model.
     * @param array $fields The fields to include in the migration.
     * @param string|null $module The module name, if applicable.
     * @return string The name of the created migration.
     */
    public function createMigration(string $pluralModel, array $fields, ?string $module): string;

    /**
     * Check if a migration already exists for the specified model.
     *
     * @param string $name The name of the model.
     * @param string|null $module The module name, if applicable.
     * @return bool True if the migration exists, false otherwise.
     */
    public function migrationExists(string $name, ?string $module): bool;
}
