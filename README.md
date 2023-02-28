# PHP Probability Selector

![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/smoren/probability-selector)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Smoren/probability-selector-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Smoren/probability-selector-php/?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/Smoren/probability-selector-php/badge.svg?branch=master)](https://coveralls.io/github/Smoren/probability-selector-php?branch=master)
![Build and test](https://github.com/Smoren/probability-selector-php/actions/workflows/test_master.yml/badge.svg)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Selection manager for choosing next elements to use from data source based on uniform distribution of selections.

#### Infinite iteration
```php
use Smoren\ProbabilitySelector\ProbabilitySelector;

$ps = new ProbabilitySelector([
    // data     // weight  // initial usage counter
    ['first',   1,         0],
    ['second',  2,         0],
    ['third',   3,         4],
]);

foreach ($ps as $datum) {
    echo "{$datum}, ";
}
// second, second, first, second, third, third, second, first, third, second, third, third, second, first, third, ...
```

#### Iteration limit and export
```php
use Smoren\ProbabilitySelector\ProbabilitySelector;

$ps = new ProbabilitySelector([
    // data     // weight
    ['first',   1],
    ['second',  2],
]);
foreach ($ps->getIterator(6) as $datum) {
    echo "{$datum}, ";
}
// second, second, first, second, second, first

print_r($ps->export());
/*
[
    ['first',  1, 2],
    ['second', 2, 4],
]
 */
```

#### Single decision
```php
use Smoren\ProbabilitySelector\ProbabilitySelector;

$ps = new ProbabilitySelector([
    // data     // weight
    ['first',   1],
    ['second',  2],
]);
$ps->decide(); // second
$ps->decide(); // second
$ps->decide(); // first
```

## Unit testing
```
composer install
composer test-init
composer test
```

## Standards

PHP Probability Selector conforms to the following standards:

* PSR-1 — [Basic coding standard](https://www.php-fig.org/psr/psr-1/)
* PSR-4 — [Autoloader](https://www.php-fig.org/psr/psr-4/)
* PSR-12 — [Extended coding style guide](https://www.php-fig.org/psr/psr-12/)


## License

PHP Probability Selector is licensed under the MIT License.
