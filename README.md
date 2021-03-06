# JSON Lint for PHP [![Build Status](https://img.shields.io/travis/cmpayments/jsonlint.svg)](https://travis-ci.org/cmpayments/jsonlint)

![License](https://img.shields.io/packagist/l/cmpayments/jsonlint.svg)
[![Latest Stable Version](https://img.shields.io/packagist/v/cmpayments/jsonlint.svg)](https://packagist.org/packages/cmpayments/jsonlint)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/cmpayments/jsonlint/badges/quality-score.png)](https://scrutinizer-ci.com/g/cmpayments/jsonlint/)
[![Total Downloads](https://img.shields.io/packagist/dt/cmpayments/jsonlint.svg)](https://packagist.org/packages/cmpayments/jsonlint)
[![Reference Status](https://www.versioneye.com/php/cmpayments:jsonlint/reference_badge.svg)](https://www.versioneye.com/php/cmpayments:jsonlint/references)

JSON Lint for PHP checks a string for invalid or malformed JSON, control character error, incorrect encoding or just plain old syntax errors.
It returns comprehensive feedback in a one-line error message (one-line message especially meant for REST APIs) about the first error that occurred in the (JSON) string.
It supports both RFC 4627 and (its superseding) RFC 7159.

Usage
-----

```php
use CMPayments\JsonLint\JsonLinter;

$linter = new JsonLinter();

// example JSON
$json = '{
    "id": 2,
    "name": "JSON Parser test",
    \'dimensions\': {
        "length": 7.0,
        "width": 12.0,
        "height": 9.5
    }
}';

// returns null if it's valid json, or a ParseException object.
$result = $linter->lint($json);

// Call $result->getMessage() on the ParseException object to get a well formatted error message error like this:
// You used single quotes instead of double quotes on string 'dimensions' on line 4 at column 9. It appears you have an extra trailing comma

// Call $result->getDetails() on the exception to get more info.
$result->getDetails();

// $result->getDetails() returns;
array (size=6)
  'errorCode' => int 3
  'match' => string 'dimensions' (length=10)
  'token' => string 'INVALID' (length=7)
  'line' => int 3
  'column' => int 9
  'expected' =>
    array (size=1)
      0 => string 'STRING' (length=6)
```

Installation
------------
For a quick install with Composer use:

    $ composer require cmpayments/jsonlint

JSON Lint for PHP can easily be used within another app if you have a
[PSR-4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
autoloader, or it can be installed through [Composer](https://getcomposer.org/).

Requirements
------------

- PHP 5.4+
- [optional] PHPUnit 3.5+ to execute the test suite (phpunit --version)

Submitting bugs and feature requests
------------------------------------

Bugs and feature request are tracked on [GitHub](https://github.com/cmpayments/jsonlint/issues)

Todo
----

- [ ] Add support for keys without double quotes*

*RFC 4627 states that an object is an unordered collection of zero or more key/value pairs, where a key is a string and a value is a string, number, boolean, null, object, or array.
This JSON linter tends to be more lenient towards keys without quotes in order to accept request from a JavaScript frontend.

Author
------

Boy Wijnmaalen - <boy.wijnmaalen@cmtelecom.com> - <https://twitter.com/boywijnmaalen>

License
-------

JSON Lint is licensed under the MIT License - see the LICENSE file for details

Acknowledgements
----------------

This library is based on [Seldaek/jsonlint](https://github.com/Seldaek/jsonlint) and [zaach/jsonlint](https://github.com/zaach/jsonlint).
