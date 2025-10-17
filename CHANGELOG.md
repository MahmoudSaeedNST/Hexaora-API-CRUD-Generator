# Changelog

All notable changes to `hexaora/api-crud-generator` will be documented in this file.

## [1.1.0]

### Major New Features

#### Policy Generation System
- **Dual-mode policy generation**: Default Laravel policies or Spatie permission integration
- Auto-generates `AuthServiceProvider` if missing
- Automatic policy registration in `AuthServiceProvider`
- Support for all standard policy methods: `viewAny`, `view`, `create`, `update`, `delete`, `restore`, `forceDelete`
- Configurable via `config/hexaora.php`

#### Factory Generation with Intelligent Mapping
- **Smart factory generation** with intelligent Faker method mapping
- Auto-detects field patterns (email, phone, address, url, etc.)
- Type-aware faker methods (string, text, integer, decimal, boolean, date, etc.)
- Modular factory structure within modules
- Global factory linker file (`database/api_factories.php`)
- Support for foreign key relationships via `Model::factory()`

#### Seeder Generation System
- Module-level seeder generation
- Global seeder registry (`database/api_seeders.php`)
- Master `ApiSeeder` class for running all module seeders at once
- Configurable factory count
- **Permission seeder generation** (Spatie mode) - auto-creates permissions

### Added

- New command flag: `--policy` - Generate policy class
- New command flag: `--factory` - Generate factory class with intelligent faker mapping
- New command flag: `--seeder` - Generate seeder class
- New command flag: `--all` - Generate everything (policy, factory, seeder)
- New stub: `policy.stub` - Default Laravel policy
- New stub: `policy-spatie.stub` - Spatie permission-based policy
- New stub: `auth-provider.stub` - AuthServiceProvider template
- New stub: `factory.stub` - Smart factory template
- New stub: `seeder.stub` - Seeder template
- New stub: `permission-seeder.stub` - Spatie permission seeder
- Global linker files for factories and seeders
- Configuration options for policies, factories, and seeders

### 🔧 Configuration

New configuration sections in `config/hexaora.php`:

```php
'policies' => [
    'spatie' => false,           // Enable Spatie integration
    'namespace' => '...',        // Customizable namespace
    'auto_register' => true,     // Auto-register policies
],

'factories' => [
    'count' => 10,              // Default factory count
    'namespace' => '...',       // Customizable namespace
],

'seeders' => [
    'namespace' => '...',       // Customizable namespace
],
```

### Documentation

- Updated README with v1.1.0 features
- Added comprehensive examples for new flags
- Documented faker field mapping
- Added configuration guide
- Explained global linker file system

### Factory Features

Field pattern recognition:
- Email fields → `faker->unique()->safeEmail()`
- Phone fields → `faker->phoneNumber()`
- Address fields → `faker->address()`
- URL/website fields → `faker->url()`
- Image fields → `faker->imageUrl()`
- Title/name fields → `faker->words(3, true)`
- Description/content → `faker->paragraph()`
- Price/decimal → `faker->randomFloat()`
- Boolean → `faker->boolean()`
- Dates → `faker->date()` / `faker->dateTime()`
- Foreign keys → `RelatedModel::factory()`

### Fixed

- **Route file overwriting issue**: Route files now intelligently append new routes instead of prompting to overwrite
  - Routes from multiple entities in the same module are now preserved
  - Use statements are automatically added after existing imports
  - No more prompts when generating additional entities in the same module
  - Duplicate routes are prevented with smart detection
  - Supports unlimited entities per module route file

###  Improvements

- Better modular organization with Database folders
- Non-intrusive defaults (no forced dependencies)
- Flexible Spatie integration (optional)
- Cleaner file structure
- Intelligent route appending with proper use statement management

### Architecture Improvements

- **Generator Pattern Implementation**: New architecture for extensible code generation
  - `GeneratorInterface` - Contract for all generators
  - `BaseGenerator` - Abstract base class with shared functionality
  - Individual generator classes: `PolicyGenerator`, `FactoryGenerator`, `SeederGenerator`, `PermissionSeederGenerator`
- **Service Layer**: Reusable components
  - `StubProcessor` - Centralized stub loading and processing
  - `FileManager` - Unified file operations with error handling
- **Helper Classes**: Specialized utilities
  - `FieldMapper` - Intelligent field-to-faker mapping (25+ patterns)
  - `LinkerManager` - Global file management for factories, seeders, and policies
- **Code Quality**: 26% reduction in main command file (1000+ lines → 740 lines)
- **SOLID Principles**: Applied throughout new architecture
- **Testability**: New components are fully testable via dependency injection

### New File Structure

```
app/Modules/YourModule/
├── Domain/
│   └── Policies/               New
│       └── EntityPolicy.php
├── Database/                   New
│   ├── factories/
│   │   └── EntityFactory.php
│   └── seeders/
│       ├── EntitySeeder.php
│       └── EntityPermissionSeeder.php

database/
├── api_factories.php           New
├── api_seeders.php             New
└── seeders/
    └── ApiSeeder.php           New
```


## [1.0.0]

### Added
- Initial release
- Complete CRUD generation with Clean Architecture
- Modular structure support
- API versioning capabilities
- Repository pattern implementation
- Form request validation classes
- API resource classes
- Migration generation with field definitions
- Route file generation
- Support for soft deletes
- Pagination support
- Multiple field types (string, text, integer, boolean, decimal, timestamp, date, foreignId)
- Field modifiers (unique, cascade, nullOnDelete, restrict)
- Artisan command `make:hexaora`
- Comprehensive documentation

### Features
- Generate 10+ files per entity
- Domain-Driven Design structure
- Clean Architecture principles
- Customizable stubs
- Force overwrite option
- Laravel 11.x and 12.x support