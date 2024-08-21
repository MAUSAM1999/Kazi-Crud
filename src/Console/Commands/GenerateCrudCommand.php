<?php

namespace Kazi\Crud\Console\Commands;

use Kazi\Crud\Services\CrudOperationService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateCrudCommand extends Command
{
    /** Documentation for laravel 11.X
     *
     * --disable="migration,model,create_request,update_request,list_resource,detail_resource,controller,route" ----- optional
     * --fields="name:string,email:string,password:string" ----- optional
     * --methods="index,getAll,store,update,delete,show,changeStatus,getMetaData"  ---- optional
     * always use {model} name is small case in singular word  ---- required
     * soft delete, extra, created_by, updated_by are set in model and migration by default
     *
     * to use modules path modules package should be installed
     * {module} name of module
     *
     * before using medias in filed make sure you have installed plank/Mediable package
     * make sure your all logic for media has been set,
     * --fields="medias:multiple" for multiple images
     * --fields="medias:single" for single image
     *
     * **/
    protected $signature = 'generate:crud {model} {--module=} {--disable=} {--fields=} {--methods=}';
    protected $description = 'Generate Required essentials for CRUD Operations';

    protected array $features = [
        'migration',
        'model',
        'create_request',
        'update_request',
        'list_resource',
        'detail_resource',
        'controller',
        'route',
    ];

    public function handle(): void
    {
        $name = Str::camel($this->argument('model'));
        $module = $this->option('module');

        $pluralModel = Str::plural($this->argument('model'));
        $fields = $this->option('fields') ? explode(',', $this->option('fields')) : [];
        $methods = $this->option('methods');
        $disabledFeatures = array_map('trim', $this->option('disable') ? explode(',', $this->option('disable')) : []);
        $enabledFeatures = array_diff($this->features, $disabledFeatures);

        $this->info('Command Executing ...');
        $service = new CrudOperationService();

        // remove default fields
        $fields = $service->removeDefaultFields($fields);

        // module verification
        if ($module) {
            $module_path = "Modules/{$module}";
            if (is_dir($module_path)) {
                foreach ($enabledFeatures as $feature) {
                    $service->{'handle' . Str::studly($feature)}($name, $pluralModel, $fields, $methods, $module);
                }
            } else {
                $this->error("\n\nThe module '{$module}' does not exist.");
            }
        } else {
            foreach ($enabledFeatures as $feature) {
                $service->{'handle' . Str::studly($feature)}($name, $pluralModel, $fields, $methods, null);
            }
        }


        $this->info("\n\nCommand Executed Successfully");
    }
}
