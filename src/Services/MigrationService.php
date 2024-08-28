<?php

namespace YajTech\Crud\Services;

use Illuminate\Support\Str;
use YajTech\Crud\Helper\FieldFormatHelper;
use YajTech\Crud\Helper\GlobalHelper;
use YajTech\Crud\Interfaces\MigrationServiceInterface;
use YajTech\Crud\Traits\PathManager;

class MigrationService implements MigrationServiceInterface
{
    use PathManager;

    const type = 'migration';

    /**
     * Create a migration for the specified model.
     *
     * @param string $pluralModel The pluralized name of the model.
     * @param array $fields The fields to include in the migration.
     * @param string|null $module The module name, if applicable.
     * @return string The name of the created migration.
     */
    public function createMigration(string $pluralModel, array $fields, ?string $module): string
    {
        // Generate the migration fields as a string
        $migrationFields = FieldFormatHelper::generateMigrationFields($fields);
        // Create a timestamped migration file name
        $migrationName = date('Y_m_d_His') . '_create_' . Str::snake($pluralModel) . '_table.php';
        $migrationPath = $this->getFullPath($migrationName, $module, $this::type);

        // Generate the migration file content
        $stubPath = $this->getStubPath($this::type);
        $migrationContent = GlobalHelper::generateFromStub($stubPath, [
            'name' => $pluralModel,
            'fields' => $migrationFields
        ]);

        // Save the migration content to the file
        file_put_contents($migrationPath, $migrationContent);

        return $migrationPath;
    }

    /**
     * Check if a migration already exists for the specified model.
     *
     * @param string $name The name of the model.
     * @param string|null $module The module name, if applicable.
     * @return bool True if the migration exists, false otherwise.
     */
    public function migrationExists(string $name, ?string $module): bool
    {
        $migrationName = "*_create_" . $name . "_table.php";
        $migrationPath = $this->getPath($this::type, $module);

        return count(glob("$migrationPath/$migrationName")) > 0;
    }
}
