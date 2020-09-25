# Laravel computed attributes

[![Latest Version on Packagist](https://img.shields.io/packagist/v/korridor/laravel-computed-attributes?style=flat-square)](https://packagist.org/packages/korridor/laravel-computed-attributes)
[![License](https://img.shields.io/packagist/l/korridor/laravel-computed-attributes?style=flat-square)](license.md)
[![TravisCI](https://img.shields.io/travis/korridor/laravel-computed-attributes?style=flat-square)](https://travis-ci.org/korridor/laravel-computed-attributes)
[![Codecov](https://img.shields.io/codecov/c/github/korridor/laravel-computed-attributes?style=flat-square)](https://codecov.io/gh/korridor/laravel-computed-attributes)
[![StyleCI](https://styleci.io/repos/226346821/shield)](https://styleci.io/repos/226346821)

Laravel package that adds computed attributes to eloquent models.
A computed attribute is an accessor where the value is saved in the database.
The value can be regenerated or validated at any time.
This can increase performance (no calculation at every get/fetch) and it can simplify querying the database (f.e. complex filter system). 
``
## Installation

You can install the package via composer with following command:

```bash
composer require korridor/laravel-computed-attributes
```

### Requirements

This package is tested for the following Laravel versions:

 - 8.*
 - 7.*
 - 6.*
 - 5.8.*
 - 5.7.* (stable only)
 
## Usage examples

See folder `tests/TestEnvironment.

## Contributing

I am open for suggestions and contributions. Just create an issue or a pull request.

### Testing

```bash
composer test
composer test-coverage
```

### Codeformatting/Linting

```bash
composer fix
composer lint
```

## License

This package is licensed under the MIT License (MIT). Please see [license file](license.md) for more information.
