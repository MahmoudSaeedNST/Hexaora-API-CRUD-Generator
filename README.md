# Hexaora API CRUD Generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hexaora/api-crud-generator.svg?style=flat-square)](https://packagist.org/packages/hexaora/api-crud-generator)
[![Total Downloads](https://img.shields.io/packagist/dt/hexaora/api-crud-generator.svg?style=flat-square)](https://packagist.org/packages/hexaora/api-crud-generator)
[![License](https://img.shields.io/packagist/l/hexaora/api-crud-generator.svg?style=flat-square)](https://packagist.org/packages/hexaora/api-crud-generator)

A Laravel CRUD generator that follows Clean Architecture principles with modular structure, API versioning, and comprehensive scaffolding. Generate complete CRUD operations with controllers, services, repositories, models, requests, resources, migrations, and routes in seconds.

## Features

- 🏗️ **Clean Architecture**: Follows Domain-Driven Design principles
- 📦 **Modular Structure**: Organizes code into logical modules
- 🚀 **API Versioning**: Built-in support for API versioning
- 🔄 **Repository Pattern**: Includes repository interfaces and implementations
- ✅ **Request Validation**: Generates form request classes with validation rules
- 📄 **API Resources**: Creates Eloquent API resource classes
- 🗄️ **Migration Support**: Generates database migrations with field definitions
- 🛣️ **Route Generation**: Creates route files with proper namespacing
- � **Policy Generation**: Auto-generates authorization policies (with optional Spatie support)
- 🏭 **Factory Generation**: Creates smart model factories with intelligent faker mapping
- 🌱 **Seeder Generation**: Generates database seeders with global linking
- �🔧 **Configurable**: Support for soft deletes, pagination, field types, and more
- 📚 **Comprehensive**: Generates 10+ files per entity in proper structure

## Requirements

- PHP 8.2 or higher
- Laravel 11.0 or 12.0

## Installation

You can install the package via Composer:

```bash
composer require hexaora/api-crud-generator
```

The package will automatically register the service provider.

Optionally, you can publish the stubs and config file:

```bash
# Publish stubs 
php artisan vendor:publish --tag="hexaora-stubs"

# Publish config 
php artisan vendor:publish --tag="hexaora-config"
```

## Usage

### Basic Usage

Generate a complete CRUD for a `Product` entity in a `Catalog` module:

```bash
php artisan make:hexaora Product --module=Catalog --fields="name:string,price:decimal:10,2,description:text"
```

This will generate:
- Model (`app/Modules/Catalog/Domain/Models/Product.php`)
- Repository Interface (`app/Modules/Catalog/Domain/Repositories/ProductRepositoryInterface.php`)
- Repository Implementation (`app/Modules/Catalog/Infrastructure/Repositories/ProductRepository.php`)
- Service Class (`app/Modules/Catalog/Application/Services/ProductService.php`)
- API Resource (`app/Modules/Catalog/Infrastructure/Resources/ProductResource.php`)
- Store Request (`app/Modules/Catalog/Application/Requests/ProductStoreRequest.php`)
- Update Request (`app/Modules/Catalog/Application/Requests/ProductUpdateRequest.php`)
- Controller (`app/Modules/Catalog/Presentation/Controllers/ProductController.php`)
- Migration (`database/migrations/xxxx_create_products_table.php`)
- Routes (`app/Modules/Catalog/routes/api.php`)

### Advanced Usage

#### Generate Everything at Once (New in v1.1.0)

```bash
php artisan make:hexaora Product --module=Inventory --fields="name:string,price:decimal:10,2" --all
```

The `--all` flag generates:
- All core CRUD files (model, controller, service, repository, etc.)
- Policy class with authorization logic
- Factory with intelligent faker field mapping
- Seeder for testing data
- Permission seeder (if Spatie mode is enabled)

#### With Policy Authorization (New in v1.1.0)

```bash
php artisan make:hexaora Product --module=Catalog --fields="name:string,price:decimal:10,2" --policy
```

Generates a policy class and auto-registers it in `AuthServiceProvider`. For Spatie permission integration, enable in config:

```php
// config/hexaora.php
'policies' => [
    'spatie' => true,  // Enable Spatie permissions
],
```

#### With Factory and Seeder (New in v1.1.0)

```bash
php artisan make:hexaora Product --module=Inventory --fields="name:string,sku:string:unique,price:decimal:10,2,description:text" --factory --seeder
```

This generates:
- Smart factory with intelligent faker methods based on field types and names
- Seeder that uses the factory
- Global linker files (`database/api_factories.php`, `database/api_seeders.php`)
- Master `ApiSeeder` class

#### With API Versioning

```bash
php artisan make:hexaora User --module=Auth --api-version=v1 --fields="name:string,email:string:unique,password:string"
```

#### With Soft Deletes

```bash
php artisan make:hexaora Post --module=Blog --softdeletes --fields="title:string,content:text,published_at:timestamp"
```

#### Without Pagination

```bash
php artisan make:hexaora Category --module=Blog --no-pagination --fields="name:string:unique,slug:string:unique"
```

#### Complex Field Types

```bash
php artisan make:hexaora Order --module=Sales --fields="customer_id:foreignId:cascade,total:decimal:10,2,status:string,notes:text,shipped_at:timestamp"
```

### Available Field Types

| Type | Example | Description |
|------|---------|-------------|
| `string` | `name:string` | VARCHAR(255) |
| `text` | `description:text` | TEXT |
| `integer` | `quantity:integer` | INTEGER |
| `boolean` | `is_active:boolean` | BOOLEAN |
| `decimal` | `price:decimal:10,2` | DECIMAL(10,2) |
| `timestamp` | `published_at:timestamp` | TIMESTAMP |
| `date` | `birth_date:date` | DATE |
| `foreignId` | `user_id:foreignId:cascade` | Foreign key with constraint |

### Field Modifiers

- `unique` - Adds unique constraint
- `cascade` - For foreign keys, cascade on delete
- `nullOnDelete` - For foreign keys, set null on delete
- `restrict` - For foreign keys, restrict on delete

### Command Options

| Option | Description | Example |
|--------|-------------|---------|
| `--module` | Module name (required) | `--module=Blog` |
| `--fields` | Comma-separated field definitions | `--fields="name:string,email:string"` |
| `--api-version` | API version | `--api-version=v1` |
| `--no-pagination` | Disable pagination | `--no-pagination` |
| `--softdeletes` | Add soft deletes | `--softdeletes` |
| `--policy` | Generate policy class ⭐ New | `--policy` |
| `--factory` | Generate factory class ⭐ New | `--factory` |
| `--seeder` | Generate seeder class ⭐ New | `--seeder` |
| `--all` | Generate everything (policy, factory, seeder) ⭐ New | `--all` |
| `--force` | Overwrite existing files | `--force` |

## Post-Generation Setup

### 1. Run Migration

```bash
php artisan migrate
```

### 2. Bind Repository in Service Provider

Add to your `app/Providers/AppServiceProvider.php`:

```php
public function register()
{
    $this->app->bind(
        \App\Modules\YourModule\Domain\Repositories\YourEntityRepositoryInterface::class,
        \App\Modules\YourModule\Infrastructure\Repositories\YourEntityRepository::class
    );
}
```

### 3. Include Routes

Add to your main `routes/api.php`:

```php
// Include module routes
require app_path('Modules/YourModule/routes/api.php');
```

Or for versioned APIs:

```php
// Include versioned module routes
require app_path('Modules/YourModule/routes/api_v1.php');
```

### 4. Seed Test Data (Optional - v1.1.0+)

If you generated factories and seeders, run:

```bash
# Seed all API module data at once
php artisan db:seed --class=ApiSeeder

# Or seed specific module
php artisan db:seed --class=\\App\\Modules\\YourModule\\Database\\Seeders\\YourEntitySeeder
```

## Generated File Structure

```
app/Modules/YourModule/
├── Application/
│   ├── Services/
│   │   └── EntityService.php
│   └── Requests/
│       ├── EntityStoreRequest.php
│       └── EntityUpdateRequest.php
├── Domain/
│   ├── Models/
│   │   └── Entity.php
│   ├── Repositories/
│   │   └── EntityRepositoryInterface.php
│   └── Policies/                        ⭐ New (with --policy or --all)
│       └── EntityPolicy.php
├── Database/                             ⭐ New (with --factory or --seeder)
│   ├── factories/
│   │   └── EntityFactory.php
│   └── seeders/
│       ├── EntitySeeder.php
│       └── EntityPermissionSeeder.php    (if Spatie mode enabled)
├── Infrastructure/
│   ├── Repositories/
│   │   └── EntityRepository.php
│   └── Resources/
│       └── EntityResource.php
├── Presentation/
│   └── Controllers/
│       └── EntityController.php (or V1/Controllers/ for versioned)
└── routes/
    └── api.php (or api_v1.php for versioned)
```

### Global Files (v1.1.0+)

When using `--factory` or `--seeder`, these global files are auto-created and maintained:

```
database/
├── api_factories.php          # Links all module factories
├── api_seeders.php            # Registry of all module seeders
└── seeders/
    └── ApiSeeder.php          # Master seeder to run all module seeders
```

Run all seeders with:
```bash
php artisan db:seed --class=ApiSeeder
```

## API Endpoints

The generated controller provides these endpoints:

| Method | URI | Action | Description |
|--------|-----|--------|-------------|
| `GET` | `/api/module/entities` | `index` | List all entities |
| `POST` | `/api/module/entities` | `store` | Create new entity |
| `GET` | `/api/module/entities/{id}` | `show` | Show specific entity |
| `PUT/PATCH` | `/api/module/entities/{id}` | `update` | Update entity |
| `DELETE` | `/api/module/entities/{id}` | `destroy` | Delete entity |

For versioned APIs, the URI includes the version: `/api/v1/module/entities`

## Examples

### Complete E-commerce Product with Authorization (New in v1.1.0)

```bash
php artisan make:hexaora Product \
    --module=Catalog \
    --fields="name:string,sku:string:unique,price:decimal:10,2,cost:decimal:10,2,description:text,category_id:foreignId:cascade,is_active:boolean" \
    --all
```

This generates everything including policy, factory, and seeder!

### Blog System with Testing Data

```bash
php artisan make:hexaora Post \
    --module=Blog \
    --softdeletes \
    --fields="title:string,slug:string:unique,content:text,excerpt:text,author_id:foreignId:cascade,published_at:timestamp,is_featured:boolean" \
    --factory \
    --seeder
```

### User Management with Policies

```bash
php artisan make:hexaora User \
    --module=Auth \
    --api-version=v1 \
    --fields="name:string,email:string:unique,email_verified_at:timestamp,password:string" \
    --policy
```

## Configuration

Publish the config file to customize behavior:

```bash
php artisan vendor:publish --tag="hexaora-config"
```

### Available Configuration Options

```php
// config/hexaora.php

return [
    'module_namespace' => 'App\\Modules',
    
    'pagination' => [
        'per_page' => 15,
    ],
    
    'policies' => [
        'spatie' => false,           // Enable Spatie permission integration
        'namespace' => 'App\\Modules\\{module}\\Domain\\Policies',
        'auto_register' => true,     // Auto-register in AuthServiceProvider
    ],
    
    'factories' => [
        'count' => 10,               // Default factory count in seeders
        'namespace' => 'App\\Modules\\{module}\\Database\\Factories',
    ],
    
    'seeders' => [
        'namespace' => 'App\\Modules\\{module}\\Database\\Seeders',
    ],
];
```

### Intelligent Factory Field Mapping (v1.1.0)

The factory generator intelligently maps fields to appropriate Faker methods:

| Field Pattern | Faker Method | Example |
|--------------|--------------|---------|
| `email` | `faker->unique()->safeEmail()` | john@example.com |
| `phone` | `faker->phoneNumber()` | (555) 123-4567 |
| `address` | `faker->address()` | 123 Main St, City |
| `url`, `website` | `faker->url()` | https://example.com |
| `image`, `photo` | `faker->imageUrl()` | https://via.placeholder.com/640x480 |
| `title`, `name` | `faker->words(3, true)` | Lorem Ipsum Dolor |
| `description`, `content` | `faker->paragraph()` | Long text... |
| `decimal`, `price` | `faker->randomFloat(2, 10, 1000)` | 523.45 |
| `boolean` | `faker->boolean()` | true/false |
| `date` | `faker->date()` | 2024-01-15 |
| `foreignId` | `RelatedModel::factory()` | Auto-creates relation |

### E-commerce Product Management

```bash
php artisan make:hexaora Product --module=Catalog --fields="name:string,sku:string:unique,price:decimal:10,2,cost:decimal:10,2,description:text,category_id:foreignId:cascade,is_active:boolean"
```

### Blog System

```bash
php artisan make:hexaora Post --module=Blog --softdeletes --fields="title:string,slug:string:unique,content:text,excerpt:text,author_id:foreignId:cascade,published_at:timestamp,is_featured:boolean"
```

### User Management

```bash
php artisan make:hexaora User --module=Auth --api-version=v1 --fields="name:string,email:string:unique,email_verified_at:timestamp,password:string,remember_token:string"
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you discover a security vulnerability, please send an e-mail to mahmoud@hexaora.com.

## Credits

- [Mahmoud Saeed](https://github.com/MahmoudSaeedNST)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.