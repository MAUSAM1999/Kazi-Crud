<?php

namespace Kazi\Crud\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Kazi\Crud\Services\tenant\TenantOperationService;

class GenerateTenantCommand extends Command
{
    protected $signature = 'generate:tenant';
    protected $description = 'Generate Required essentials for tenant Operations';

    public function handle(): void
    {
        $this->info('Tenant Command Executing ...');
        $service = new TenantOperationService();


        $this->info("\n\nTenant Command Executed Successfully");
    }
}
