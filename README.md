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
- 🔧 **Configurable**: Support for soft deletes, pagination, field types, and more
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
# Publish stubs (optional)
php artisan vendor:publish --tag="hexaora-stubs"

# Publish config (optional)
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
│   └── Repositories/
│       └── EntityRepositoryInterface.php
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