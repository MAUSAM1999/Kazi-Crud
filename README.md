# Kazi Crud

**Kazi Crud** is a Laravel package designed to streamline CRUD operations with customizable options for controllers,
models, and event listeners. This README will guide you through the installation, setup, and usage of the package.

## Installation and Setup

### Step 1: Add the Repository

To include the Kazi Crud package, first add the repository to your `composer.json` file:

```json
"repositories": [
{
"type": "vcs",
"url": "https://gitlab.com/yajtech/soci-crud-backend.git"
}
]
```

### Step 2: Install the Package

Run the following command in your terminal to install the package:

``` bash
composer require kazi/crud
```

### Step 3: Register api.php file path in bootstrap/app.php

Laravel 11 above :

After installation, register the service provider in your bootstrap/providers.php file:

```php

<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(DIR))
    ->withRouting(
        web: DIR.'/../routes/web.php',
        api: DIR.'/../routes/api.php', // add this file here so that application can register api.php as route file
        commands: DIR.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

```

For Laravel Below Version 11 : [ no need to add any path as it is already registered in app service provider. ]

### Step 4: Register the Service Provider

For Laravel Below Version 11 :

After installation, register the service provider in your config/app.php file:

``` php
'providers' => [
    // Other Service Providers

    \Kazi\Crud\Providers\CrudServiceProvider::class,
],
```

Laravel 11 above :

After installation, register the service provider in your bootstrap/providers.php file:

```php

return [
    App\Providers\AppServiceProvider::class,
    
    // kazi service providers
    \Kazi\Crud\Providers\CrudServiceProvider::class
];

```

Now, you're ready to use the classes provided by the package:

- Use CrudController for your controllers.
- Use CrudModel trait for your models.
- Use CrudEventListener trait for event listeners.

## Usage

### Generating CRUD Operations

To generate CRUD operations, use the following Artisan command:

```bash
php artisan generate:crud {model} {--module=} {--disable=} {--fields=} {--methods=}
```

**Parameters:**

- `{model}`: The name of the model (singular, lowercase) **(required)**
- `--module=`: Specify the module name **(optional)**
- `--disable=`: Disable specific components like migration, model, controller, etc. **(optional)**
- `--fields=`: Define the fields for your model (e.g., `name:string,email:string`) **(optional)**
- `--methods=`: Specify the methods to generate (e.g., `index,store,update`) **(optional)**

### Notes:

- Soft delete, `created_by`, and `updated_by` fields are set in the model and migration by default.
- For media management, ensure you have installed
  the [Plank Mediable](https://laravel-mediable.readthedocs.io/en/latest/installation.html) package.
- To use the module feature, install
  the [Laravel Modules](https://nwidart.com/laravel-modules/v6/installation-and-setup) package.

### Media Management

To manage media fields:

- Use `--fields="medias:multiple"` for multiple images.
- Use `--fields="medias:single"` for a single image.

Ensure that your media logic is properly configured before using these features.

## Update Kazi Crud Package With Latest Version

```bash
Composer Update
```

### Additional Documentation

For more detailed usage and advanced features, refer to the package's full documentation [here](https://sociair.com/).
