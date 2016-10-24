# SimpleFM

[![Build Status](https://travis-ci.org/soliantconsulting/SimpleFM.svg?branch=master)](https://travis-ci.org/soliantconsulting/SimpleFM)
[![Code Climate](https://codeclimate.com/github/soliantconsulting/SimpleFM/badges/gpa.svg)](https://codeclimate.com/github/soliantconsulting/SimpleFM)
[![Test Coverage](https://codeclimate.com/github/soliantconsulting/SimpleFM/badges/coverage.svg)](https://codeclimate.com/github/soliantconsulting/SimpleFM/coverage)
[![Latest Stable Version](https://poser.pugx.org/soliantconsulting/simplefm/v/stable)](https://packagist.org/packages/soliantconsulting/simplefm)
[![Latest Unstable Version](https://poser.pugx.org/soliantconsulting/simplefm/v/unstable)](https://packagist.org/packages/soliantconsulting/simplefm)
[![Total Downloads](https://poser.pugx.org/soliantconsulting/simplefm/downloads)](https://packagist.org/packages/soliantconsulting/simplefm)
[![License](https://poser.pugx.org/soliantconsulting/simplefm/license)](https://packagist.org/packages/soliantconsulting/simplefm)

SimpleFM is a fast, convenient and free tool designed by [Soliant Consulting, Inc.][1] to facilitate connections between PHP web applications and FileMaker Server.

SimpleFM is a lightweight PHP package that uses the [FileMaker Server][2] XML API. The FMS XML API is commonly referred to as Custom Web Publishing (CWP for short).

SimpleFM is [Composer][3] friendly, making it a snap to use with all [PHP-FIG][4] frameworks, including [Zend Framework][5], Symfony, Laravel, Slim, and many more.

See also, the [SimpleFM-skeleton][6] demo application which illustrates use of SimpleFM in a middleware Zend Framework application.

## Features

### Easy to Integrate

- PSR-4 autoloading ([Composer][3] ready).
- Can be used on it's own or with any service or middleware, such as [Apigility][7] or Stratigility.

### CWP Debugger

- Easily see the underlying API command formatted as a URL for easy troubleshooting
- File Maker error codes are translated to understandable error messages

## Simplicity and Performance

SimpleFM was written with simplicity as the guiding principle. We have informally benchmarked it, and obtained faster results for the same queries compared to the two most common CWP PHP alternatives.

## System Requirements

SimpleFM, the examples and this documentation are tailored for PHP 7.0 and FileMaker Sever 12

- PHP 7.0+
- FileMaker Server 12+

With minimum effort, it should theoretically work with any version of FileMaker server that uses fmresultset.xml grammar, however, backward compatibility is not verified or maintained.

## License

SimpleFM is free for commercial and non-commercial use, licensed under the business-friendly standard MIT license.

## Installation

Install via composer:

```bash
composer require soliantconsulting/simplefm
```

## Documentation

Documentation builds are available at:

- https://simplefm.readthedocs.org

You can also build the documentation locally via [MkDocs](http://www.mkdocs.org):

```bash
$ mkdocs serve
```

[1]: http://www.soliantconsulting.com
[2]: http://www.filemaker.com/products/filemaker-server/
[3]: https://getcomposer.org/doc/00-intro.md
[4]: http://www.php-fig.org/
[5]: http://framework.zend.com/
[6]: https://github.com/soliantconsulting/SimpleFM-skeleton
[7]: https://apigility.org/
