# Changelog

## v1.1.2

### Fixed
* Prevented `Class "" does not exist` error by ensuring `Route::middleware()` receives an empty array when middleware is null.

## v1.1.1

### Fixed
* Resolved an issue in `PluginLoader` where attempting to instantiate plugin classes with incorrect constructors or abstract definitions caused errors.
* Now using Reflection to validate that classes extend/implement `Plugin` and have compatible constructors before instantiation.

## v1.1.0

### Added
* plugin:install Artisan command.
* New methods for easier access to plugin data.
* Configurable plugin descriptor suffix.

### Improved
* Optimized plugin instance initialization process.

### Fixed
* Corrected plugin configuration source priority.

## v1.0.0

Initial commit.
