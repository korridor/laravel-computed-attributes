# Laravel computed attributes

[![Latest Version on Packagist](https://img.shields.io/packagist/v/korridor/laravel-computed-attributes?style=flat-square)](https://packagist.org/packages/korridor/laravel-computed-attributes)
[![License](https://img.shields.io/packagist/l/korridor/laravel-computed-attributes?style=flat-square)](license.md)
[![Codecov](https://img.shields.io/codecov/c/github/korridor/laravel-computed-attributes?style=flat-square)](https://codecov.io/gh/korridor/laravel-computed-attributes)
[![TravisCI](https://img.shields.io/travis/korridor/laravel-computed-attributes?style=flat-square)](https://travis-ci.org/korridor/laravel-computed-attributes)
[![StyleCI](https://styleci.io/repos/226346821/shield)](https://styleci.io/repos/226346821)

Laravel package that adds computed attributes to eloquent models.
A computed attribute is an accessor where the value is saved in the database.
The value can be regenerated or validated at any time.
This can increase performance (no calculation at every get/fetch) and it can simplify querying the database (f.e. complex filter system). 

## Installation

You can install the package via composer with following command:

```bash
composer require korridor/laravel-computed-attributes
```

### Requirements

This package is tested for the following Laravel versions:

 - 8.* (PHP 7.3, 7.4, 8.0)
 - 7.* (PHP 7.2, 7.3, 7.4)
 - 6.* (PHP 7.2, 7.3)
 
## Usage examples

Here is an example of two computed attributes `complex_calculation` and `sum_of_votes`.
The functions `getComplexCalculationComputed` and `getSumOfVotesComputed` are calculating the computed attributes.

```php
use Korridor\LaravelComputedAttributes\ComputedAttributes;

class Post {

    use ComputedAttributes;

    /**
     * The attributes that are computed. (f.e. for performance reasons)
     * These attributes can be regenerated at any time.
     *
     * @var array
     */
    protected $computed = [
        'complex_calculation',
        'sum_of_votes',
    ];

    /*
     * Computed attributes.
     */

    /**
     * @return int
     */
    public function getComplexCalculationComputed(): int
    {
        return 1 + 2;
    }

    /**
     * @return int
     */
    public function getSumOfVotesComputed(): int
    {
        return $this->votes->sum('rating');
    }
    
    // ...
}
```



https://laravel.com/docs/8.x/eloquent#events

```php
/**
 * Boot function from laravel.
 */
protected static function boot()
{
    static::saving(function (Post $model) {
        $model->setComputedAttributeValue('sum_of_votes');
    });
    parent::boot();
}
```

For the whole code of this very simple example see the `tests/TestEnvironment` folder.

## Commands

### computed-attributes:generate

This command (re-)calculates the values of the computed attributes and saves the new value.

#### Query optimization

You can use the `computedAttributesGenerate` scope in any model using the `ComputedAttributes` trait to extend the query that fetches the models for the calculation.

```php
use Illuminate\Database\Eloquent\Builder;

// ...

/**
 * This scope will be applied during the computed property generation with artisan computed-attributes:generate.
 *
 * @param Builder $builder
 * @param array $attributes Attributes that will be generated.
 * @return Builder
 */
public function scopeComputedAttributesGenerate(Builder $builder, array $attributes): Builder
{
    if (in_array('sum_of_votes', $attributes)) {
        return $builder->with('votes');
    }

    return $builder;
}
```

### computed-attributes:validate

This command validates the current values of the computed attributes.

#### Query optimization

```php
use Illuminate\Database\Eloquent\Builder;

// ...

/**
 * This scope will be applied during the computed property validation with artisan computed-attributes:validate.
 *
 * @param Builder $builder
 * @param array $attributes Attributes that will be validated.
 * @return Builder
 */
public function scopeComputedAttributesValidate(Builder $builder, array $attributes): Builder
{
    if (in_array('sum_of_votes', $attributes)) {
        return $builder->with('votes');
    }

    return $builder;
}
```

## Contributing

I am open for suggestions and contributions. Just create an issue or a pull request.

### Local docker environment

The `docker` folder contains a local docker environment for development.
The docker workspace has composer and xdebug installed.

```bash
docker-compose run workspace bash
```

### Testing

The `composer test` command runs all tests with [phpunit](https://phpunit.de/).
The `composer test-coverage` command runs all tests with phpunit and creates a coverage report into the `coverage` folder.

### Codeformatting/Linting

The `composer fix` command formats the code with [php-cs-fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer).
The `composer lint` command checks the code with [phpcs](https://github.com/squizlabs/PHP_CodeSniffer).

## License

This package is licensed under the MIT License (MIT). Please see [license file](license.md) for more information.
