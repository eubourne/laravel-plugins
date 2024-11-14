<p style="text-align: center"><img src="/assets/laravel-plugins-card.jpg" alt="Laravel Plugins"></p>

# Laravel plugins

**Laravel Plugins** is a package designed to organize Laravel code into modules, allowing for better
separation of features and easier code management. This package helps you structure your project into logical 
modules, such as Cart, Catalog, Blog, etc., where each module contains its own service providers, routes, and
other resources.

- [Features](#features)
- [Installation](#installation)
- [Basic Usage](#basic-usage)
    - [Creating a Module](#creating-a-module)
    - [Directory Structure](#directory-structure)
    - [Registering Module Resources](#registering-module-resources)
        - [_Service Providers_](#service-providers)
        - [_Routes_](#routes)
        - [_Multiple Module Groups_](#multiple-module-groups)
    - [Example Module Definition](#example-module-definition)
- [Optimization](#optimization)
- [License](#license)
- [Contributing](#contributing)
- [Contact](#contact)

## Features
- **Organize project code by modules:** Group related code in dedicated module folders, improving structure and maintainability.
- **Automatic module detection:** The package automatically scans the modules directory for defined modules.
- **Module-specific resource loading:** Each module can contain service providers, routes, translations, and channels that are loaded individually.
- **Module structure:** Modules are self-contained and follow a predictable directory structure for easier development and scaling.

## Installation

To install the package, run:

```bash
composer require eubourne/laravel-plugins
```

This package makes use of [Laravels package auto-discovery mechanism](https://medium.com/@taylorotwell/package-auto-discovery-in-laravel-5-5-ea9e3ab20518),
so you don't have to manually register its service provider.

Run the following artisan command to publish configuration file:
```bash
php artisan vendor:publish --provider="EuBourne\LaravelPlugins\PluginServiceProvider" --tag=config
```
It will create `config/plugins.php` file with reasonable defaults.

Instruct composer autoload in `composer.json` where to look for your module files:
```php
"autoload": {
    "psr-4": {
        "Modules\\": "modules/",
    }
}
```
> **NOTE:**
>
> The path should match the value specified in the `groups.*.path` section of your `config/plugins.php` file. If you have
> multiple groups, you need to add a separate `psr-4` entry in `package.json` for each group.

After updating the `composer.json`, run:
```bash
composer dump-autoload
```

## Basic Usage

### Creating a Module
1. **Add a `modules` directory:** In the root of your project, create a `modules` folder if it doesn’t already exist.
2. **Create a module folder:** For each module, create a directory under `modules` (e.g., `Cart`, `Catalog`, `Blog`, etc.).
3. **Define the module:** Inside each module folder, create a `<ModuleName>Module.php` file. This file should extend
`EuBourne\LaravelPlugins\Plugin` class, enabling it to be registered by the package.

### Directory Structure
A module typically follows this structure:
```bash
modules/
├── Cart/
│   ├── Providers/                      # Folder for service providers
│   │   └── ServiceProvider.php
│   ├── Routes/
│   │   ├── web.php                     # Web routes for the module
│   │   ├── api.php                     # API routes for the module
│   │   └── channels.php                # Broadcasting channels
│   ├── Lang/                           # Folder for translations
│   │   └── en/
│   │       └── messages.php
│   └── CartModule.php                  # Main module definition file
└── Catalog/
    ├── Providers/
    ├── Routes/
    ├── Lang/
    └── CatalogModule.php
```

### Registering Module Resources

#### Service Providers
Each module can register a main service provider by creating a `Providers/ServiceProvider.php` file inside the
module directory. The package automatically registers this provider if it’s found.

- **Additional Service Providers:** You can specify more service providers from the main service provider. For
convenience, you can inherit module service provider from `EuBourne\LaravelPlugins\BaseServiceProvider` and
override its `$providers` property with an array of service providers to register.
  ```php
  namespace Modules\Blog\Providers;

  use EuBourne\LaravelPlugins\BaseServiceProvider;

  class ServiceProvider extends BaseServiceProvider
  {
    protected array $providers = [
      BlogLoggingServiceProvider::class,
      // ...
    ]
  }
  ```
  This way both `Modules\Blog\Providers\ServiceProvider` and `Modules\Blog\Providers\BlogLoggingServiceProvider` will
  be registered.


- **Override Default Providers:** To replace auto-discovered providers with a fixed list, override the `$providers`
array in `<ModuleName>Module.php`.
  ```php
  namespace Modules\Blog;
  
  class BlogModule extends EuBourne\LaravelPlugins\Plugin
  {
    protected array $providers = [
      \Modules\Blog\Providers\BlogLoggingServiceProvider::class,
      \Modules\Blog\Providers\ServiceProvider::class,
    ];
  }
  ```

  > **NOTE:**
  > 
  > All module service providers should extend `Illuminate\Support\ServiceProvider`.

#### Routes
By default, the package registers routes and applies specific middleware based on file names:

- **Web routes:** The package loads routes from `web.php` and applies the `web` middleware.
- **API routes:** All routes defined in files that match `api*.php` filename pattern (i.e.: `api.php`, `api_v1.php`,
`api_admin.php`, etc.) will be loaded with the `api` middleware.

To configure additional route file names:

1. Edit the `config/plugins.php` file.
2. Update the `routes` section to add or modify route files. For example:
  ```php
  'routes' => [
      'web' => [
          'filename' => 'web*.php' // Allows `web.php`, `web_admin.php`, etc.
      ],
      'api' => [
          'filename' => 'api*.php' // Allows `api.php`, `api_v1.php`, etc.
      ]
  ]
  ```

#### Multiple Module Groups
You can define multiple module groups in the `groups` section of the configuration file. Each group may have its own
root directory and unique route configurations.

Example configuration:
```php
'groups' => [
    'modules' => [
        'path' => 'modules', // modules root directory
        'routes' => [
            'web' => [
                'filename' => 'web*.php'
            ],
            'api' => [
                'filename' => 'api*.php'
            ]
        ]
    ],
    'widgets' => [
        'path' => 'widgets',
        'routes' => [
            'api' => [
                'filename' => 'api_v*.php' // Specific to widgets group
            ]
        ]
    ]
]
```
In this example:
- **`modules`** is the main group for primary modules.
- **`widgets`** is a secondary group with its own routing configuration. Routes like `api_v1.php` and `api_v2.php` are
loaded only within widgets.

### Example Module Definition

Here’s a sample `BlogModule.php` for the `Blog` module:
```php
namespace Modules\Blog;

use EuBourne\LaravelPlugins\Plugin;

class BlogModule extends Plugin
{
}
```

`Blog` module service provider, placed in `modules/Blog/Providers`:
```php
namespace Modules\Home\Providers;

use EuBourne\LaravelPlugins\BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        // Register services
    }

    public function boot(): void
    {
        // Load resources
    }
}
```

## Optimization
Discovering modules on each request may impact performance due to file scan and read operations. To enhance
performance, cache module discovery with the following command:

```bash
php artisan plugin:cache
```

To clear the cached module data and reset module discovery, run:

```bash
php artisan plugin:clear
```

The package also supports standard Laravel optimization commands:

```bash
php artisan optimize
php artisan optimize:clear
```

For information about discovered modules, two commands are available:
```bash
# Displays a list of all discovered plugins
php artisan plugin:list

# Shows details for a specific plugin
php artisan plugin {plugin_key}
```

## License
This package is open-source and available for free under the [MIT license](http://opensource.org/licenses/MIT).

## Contributing
Feel free to submit issues or pull requests to help improve this package.

## Contact
For more information or support, please reach out via GitHub or email.
