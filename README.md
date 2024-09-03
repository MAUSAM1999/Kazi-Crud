# YajTech Crud v1.0.3

**YajTech Crud** is a Laravel package designed to streamline CRUD operations with customizable options for controllers,
models, and event listeners. This README will guide you through the installation, setup, and usage of the package.

## Installation and Setup

### Step 1: Add the Repository

To include the YajTech Crud package, first add the repository to your `composer.json` file:

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
composer require yajtech/crud
```

### Step 3:  Install API For Laravel 11 and above only ( routes/api.php not found )

``` bash
php artisan install:api
```

- create api.php in routes/api.php
- Install and manage Laravel/sanctum

After installation, register the service provider in your bootstrap/app.php should be like below:

```php

<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(DIR))
    ->withRouting(
        web: __DIR__ .'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // add this file here so that application can register api.php as route file
        commands: __DIR__.'/../routes/console.php',
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

    \YajTech\Crud\Providers\CrudServiceProvider::class,
],
```

Laravel 11 above :

After installation, register the service provider in your bootstrap/providers.php file:

```php

return [
    App\Providers\AppServiceProvider::class,
    
    // YajTech Crud service providers
    \YajTech\Crud\Providers\CrudServiceProvider::class
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
- `--disable=`: Disable specific components like (
  e.g., `migration,model,create_request,update_request,list_resource,detail_resource,controller,route`)  **(optional)**
- `--fields=`: Define the fields for your model (e.g., `name:string,email:string`) **(optional)**
- `--methods=`: Specify the methods to generate (e.g., `index,getAll,store,update,delete,show,changeStatus,getMetaData`)
  **(optional)**

### Notes:

- Soft delete, `created_by`, and `updated_by` fields are set in the model and migration by default.
- For media management, ensure you have installed
  the [Plank Mediable](https://laravel-mediable.readthedocs.io/en/latest/installation.html) package.
- To use the module feature, install
  the [Laravel Modules](https://nwidart.com/laravel-modules/v6/installation-and-setup) package.

Ensure that your media logic is properly configured before using these features.

## Model Management

use Trait in each module, these below trait helps for filter, search and dynamic pagination and order by management

```php
<?php

namespace App\Models;

use YajTech\Crud\Traits\CrudEventListener;
use YajTech\Crud\Traits\CrudModel;

class ClassName extends Model
{
 use CrudModel, CrudEventListener;
 
 // other code here 
}
```

### Use `CrudEventListener`

#### Model Manage Unique Fields

If your model's database has unique fields, `CrudEventListener` can help manage them by adding random data to the unique
column of a deleted row. This ensures that the same data can be reused in another row.

Example:

```json
{
  "unique_field": "original_value_deleted_123"
}
```

#### Model Media Management

To manage media uploads using `CrudEventListener`, add the following code to the FIELDS array in your model:

```php
const FIELDS = [

    // Other fields here
    
    [
        'name' => 'upload',
        'type' => 'upload',
        'label' => 'Upload',
        'wrapper' => [
            'class' => 'col-12'
        ],
        'rules' => [
            'required' => true // This is optional; set to false if the field is not required
        ]
    ],
    [
        'name' => 'upload_multiple',
        'type' => 'upload_multiple',
        'label' => 'Upload Multiple',
        'wrapper' => [
            'class' => 'col-12'
        ],
        'rules' => [
            'required' => true // This is optional; set to false if the field is not required
        ]
    ]
];
```

### Model COLUMN Configuration

To define the columns for your CRUD table, you can use the following `COLUMNS` array configuration:

```php
const COLUMNS = [
    [
        'name' => 'sn', // used for field and name key in q-table component 
        'label' => 'SN', // used for show label
        'type' => 'sn', // type of column Example : sn, email, text etc.
        'sortable' => true, // used for enable sortable 
    ],
     [
        'name' => 'name',
        'label' => 'Name',
        'type' => 'text',
        'sortable' => true,
    ],
    [
        'name' => 'email',
        'label' => 'Email',
        'type' => 'email',
        'sortable' => true,
    ],   
];
```

### Model FIELDS Configuration

The `FIELDS` array allows you to define the input fields for your forms. Below is an example configuration:

```php
const FIELDS = [
    [
        'name' => 'name', // fro v-model
        'type' => 'text', // type of input
        'label' => 'Name', // used for show label
        'wrapper' => [  
            'class' => 'col-6' // size used
        ],
        'rules' => [
            'required' => true // false if filed is not required
        ],
    ],
    [
        'name' => 'role_id',
        'type' => 'select_from_model',
        'label' => 'Role',
        'attribute' => 'name',
        'wrapper' => [
            'class' => 'col-12'
        ],
        'multiple' => true
        'model' => "App\Models\Role",
        'columns' => ['id', 'name', 'display_name'],
        'rules' => [
            'required' => true
        ],
    ],
];
```

- `name` : The name of the field, used as the key for storing and retrieving the value. (v-model)
- `type` : The input type (e.g., text, email, password, select_from_model).
- `label` : The label displayed alongside the input field in the form.
- `wrapper` : Defines the CSS class for the field's wrapper, allowing for layout customization (e.g., col-6 for
  half-width fields, col-12 for full-width).
- `rules` : Validation rules for the field (e.g., required).
- `multiple` : Enable / Disable Select Multiple Data.
- `attribute` : Specific to fields that map with label key in frontend
  values from a model. entity specifies the related model, attribute defines the display attribute, and default is the
  default value.
- `model` : Specifies the model to be used for the select_from_model type field.
- `columns` : Optional array specifying the columns to be retrieved from the model.

### Model Table Configuration

The `TABLE` array defines the configuration options for the table displayed in your CRUD interface.

```php
const TABLE = [
    'add_button' => true,
    'refresh_button' => true,
    'export_button' => true,
    'filter_button' => true
];
```

- `add_button` : Enables or disables the "Add" button.
- `refresh_button` : Enables or disables the "Refresh" button.
- `export_button` : Enables or disables the "Export" button.
- `filter_button` : Enables or disables the "Filter" button.

### Model Example for YajTech

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use YajTech\Crud\Traits\CrudModel;
use YajTech\Crud\Traits\CrudEventListener;
use \Illuminate\Database\Eloquent\SoftDeletes;

class YajTech extends Model
{
    use HasFactory, CrudModel, SoftDeletes, CrudEventListener;

    const COLUMNS = [
        [
            'name' => 'sn',
            'label' => 'SN',
            'align' => 'left',
            'type' => 'text',
            'sortable' => true,
        ],
        [
            'name' => 'name',
            'label' => 'Name',
            'align' => 'left',
            'type' => 'text',
            'sortable' => true,
        ],
        [
            'name' => 'display_name',
            'label' => 'Display_name',
            'align' => 'left',
            'type' => 'text',
            'sortable' => true,
        ],
    ];
    const FIELDS = [
        [
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
            'wrapper' => [
                'class' => 'col-6',
            ],
            'rules' => [
                'required' => true,
            ],
        ],
        [
            'name' => 'display_name',
            'label' => 'Display_name',
            'type' => 'text',
            'wrapper' => [
                'class' => 'col-6',
            ],
            'rules' => [
                'required' => true,
            ],
        ],
    ];
    const TABLE = [
        'add_button' => true,
        'refresh_button' => true,
        'export_button' => true,
        'filter_button' => true,
    ];
    const FILTERS = [
        [
            'name' => 'name',
            'column' => 'name',
            'type' => 'text',
            'relation' => 'where',
            'dense' => true,
            'label' => 'Name',
            'wrapper' => [
                'class' => 'col-3',
            ],
        ],
        [
            'name' => 'display_name',
            'column' => 'display_name',
            'type' => 'text',
            'relation' => 'where',
            'dense' => true,
            'label' => 'Display_name',
            'wrapper' => [
                'class' => 'col-3',
            ],
        ],
         [
            'name' => 'role',
            'column' => 'id',
            'type' => 'select_from_model',
            'label' => 'Role',
            'attribute' => 'name',
            'relation' => 'whereHas:where',
            'query' => 'roles',
            'dense' => true,
            'wrapper' => [
                'class' => 'col-3'
            ],
            'multiple' => false,
            'model' => "App\Models\Role",
            'columns' => ['id', 'name'], // optional if empty then return all
        ]
    ];

    protected $fillable = ['name', 'display_name', 'created_by', 'updated_by', 'extra'];

    protected $casts = [
        'extra' => 'array'
    ];
}

```

## Update YajTech Crud Package With Latest Version

```bash
Composer Update
```

### Additional Documentation

For more detailed usage and advanced features, refer to the package's full
documentation [here](https://yajtechnologies.com/).
