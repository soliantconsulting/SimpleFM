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

SimpleFM is [Composer][3] friendly, making it a snap to use with all [PHP-FIG][4] frameworks, including [Zend Framework][5], Symphony, Laravel, Slim, and many more.

See also, the [SimpleFM_FMServer_Sample][6] demo application which illustrates use of SimpleFM in an MVC Zend Framework application.

## Features

### Easy to Integrate

* PSR-0 autoloading ([Composer][3] ready).
* Returns a PHP array. The result parser inside `Soliant\SimpleFM\Adapter` uses PHP5's `SimpleXML`.
* Can be used on it's own or with any service or middleware, such as [Apigility][7] or Stratigility.

### CWP Debugger

* Easily see the underlying API command formatted as a URL for easy troubleshooting
* Use the convenient errorToEnglish function to interpret FMS error codes

### Optional Zend Framework 2 Integration

* The `Soliant\SimpleFM\ZF2` package provides excellent integration with Zend Framework
* Full `Zend\Authentication\Adapter` implementation for robust authentication and session management.
* The included `AbstractEntity` makes it simple to implement Object serialization and de-serialization
* The included `AbstractGateway` provides a foundation for completely encapsulating the FileMaker XML API 

## Simplicity and Performance

SimpleFM was written with simplicity as the guiding principle. We have informally benchmarked it, and obtained faster results for the same queries compared to the two most common CWP PHP alternatives.

## System Requirements

SimpleFM, the examples and this documentation are tailored for PHP 5.5 and FileMaker Sever 12

* PHP 5.5+
* FileMaker Server 12+

With minimum effort, it should theoretically work with any version of FileMaker server that uses fmresultset.xml grammar, however, backward compatibility is not verified or maintained.

## License

SimpleFM is free for commercial and non-commercial use, licensed under the business-friendly standard MIT license.


# SimpleFM Documentation

All the examples included with SimpleFM are based the FMServer_Sample which is included with FileMaker Server 12. To use the examples it is assumed that you have FMServer_Sample running on a FileMaker 12 host with XML web publishing enabled. (You may also have any other FMS services enabled, including PHP web publishing, but only XML is required for SimpleFM.) Setup and configuration of FileMaker server is beyond the scope of this documentation.

See `/documentation/fms12_cwp_xml_en.pdf` for the official FileMaker documentation. In particular, Appendix A (page 43) contains a useful command reference.

> WARNING: Copy/paste out of the pdf documentation must be done with caution. The typsetting uses emdash, not hyphen characters. They look very similar, and this can be very hard to troubleshoot if you are not careful.

See `/documentation/simplefm_example.php` for a working PHP example that follows the basic steps shown in this Quickstart section, as well as some additional tips about usage.

## Quickstart

### Install

#### Via Composer (recommended)

```
composer require soliantconsulting/simplefm
```

#### Manually

Move the SimpleFM package in your project, and then require the classes in the classmap.

```
foreach (require(/path/to/library/autoload_classmap.php') as $classPath) {
    require_once($classPath);
}
```

### Import the Adapter

```
use Soliant\SimpleFM\Adapter;
use Soliant\SimpleFM\HostConnection;
```
    
## Basic Adapter Configuration

Create a new HostConnection object.

```
$hostConnection = new HostConnection(
    'localhost',
    'FMServer_Sample',
    'Admin',
    ''
);
```

### Instantiate the Adapter

```
$adapter = new Adapter($hostConnection);
```

### Set layout context

```
$adapter->setLayoutName('Tasks');
```
    
    
### Set command(s)

```
/**
 * @Note: See fms12_cwp_xml_en.pdf Appendix A for a complete command reference.
 * Commands that take no arguments, such as -findall, must be set with either a
 * NULL value or an empty string.
 * 
 * @WARNING: Copy/paste out of the pdf must be done with caution. The typsetting
 * uses emdash, not hyphen characters. They look very similar, and this can be
 * very hard to troubleshoot if you are not careful.
 */
$adapter->setCommandArray(
    array(
        '-max'     => 10,
        '-skip'    => 5,
        '-findall' => NULL
    )
);
```

### Execute

```
$result = $adapter->execute();
```
    
### Handle the result

```
$url          = $result->getDebugUrl();
$errorCode    = $result->getErrorCode();
$errorMessage = $result->getErrorMessage();
$errorType    = $result->getErrorType();
$count        = $result->getCount();
$fetchSize    = $result->getFetchSize();
$rows         = $result->getRows();
```

## Using the Example File

The `simplefm_example.php` file assumes that FileMaker Server is on `localhost` and has the default FMServer_Sample
file hosted. You may edit the hostname and other settings as needed if your FileMaker Server is hosted elsewhere.

In terminal, `cd` to the documentation directory that comes with SimpleFM

```
cd /path/to/simplefm/documentation
```

Next, start the built-in PHP server on an available port. Assuming port 8080 is available, run this command:

```
php -S localhost:8080
```

If the built in PHP server starts correctly, you should see a message like this in Terminal

```
PHP 5.5.20 Development Server started at Sat Jul  4 14:35:43 2015
Listening on http://localhost:8080
Document root is /path/to/simplefm/documentation
Press Ctrl-C to quit.
```

You should now be able to load <http://localhost:8080/simplefm_example.php> in a browser and experiment with it.

## About FileMaker Portals

Portals are returned as named child arrays to every record in the fetched set. Be careful about adding portals to your web layouts, as all associations are loaded eagerly, and this could bloat your result array. SimpleFM can't take advantage of techniques like lazy loading that help mitigate performance issues with large related data sets in ORMs like Doctrine, so it is up to the developer to tailor the web layouts appropriately for best performance.

There can be more than one portal on a layout. SimpleFM returns n portals for every record in the found set.

Assuming you leave rowsbyrecid as FALSE (the default setting), here is example array notation that would echo the the recid and the field value from the first portal row on the first record in the result set. Note that index, recid and modid are always properties on every parent and child row.

```
echo $rows[0]['Portal_TO_Name']['rows'][0]['recid'].'<br>';    
echo $rows[0]['Portal_TO_Name']['rows'][0]['myField'];
```

If you set rowsbyrecid to TRUE on your adapter, here is syntax that would echo the data from a portal where the parent row has recid 154 and the child row has recid 335932.

```
echo $rows[154]['Portal_TO_Name']['rows'][335932]['Related_TO_Name::fieldOnPortal'];
```

It is left to you do decide which way you want the results indexed.

## About FileMaker Repeating Fields

FileMaker supports a field configuration called "Repeating". If the field is defined as repeating in the FileMaker schema, but the layout associated with the request only defines a single repetition, SimpleFM will treat it like a normal field. Assume a repeating field named myRepeatingField is defined with three repetitions, containing the following three values in each repetition, respectively:

1. Foo
1. Bar
1. Baz

```
// If the layout only defines one repetition for the field

echo $rows[0]['myRepeatingField'];

// output:
// Foo
```

```
// If the layout defines more than one repetition for the field

echo $rows[0]['myRepeatingField'][0] . <br>;
echo $rows[0]['myRepeatingField'][1] . <br>;
echo $rows[0]['myRepeatingField'][2];

// output:
// Foo
// Bar
// Baz
```

Note that FileMaker repetitions, are 1 indexed, and of course php arrays are 0 indexed, so repetition 1 is `$myRepeatingField[0]` and so on.

## About the syntax for a fully qualified field name

In particular, you will need to understand qualified field name conventions in order to set repetitions (other than 1) on repeating fields. 

```
field-name(repetition-number)
```

If you leave off the repetition number when setting fields in your commandArray, it defaults to setting repetition 1.

Example:

```
    $adapter->setCommandArray(
        array(
            'myRepeatingField'    => 'Foo',
            'myRepeatingField(2)' => 'Bar',
            'myRepeatingField(3)' => 'Baz',
            '-new'                => null
        )
    );
```

## FileMaker Layouts

Note that fields (and field repetitions) must be on the layout you specify in a command or else they will not be accessible.

## FileMaker XML API Commands

Please be sure to read the official API Documentation Appendix A (page 45), as it is somewhat unique. Don't assume that experience writing
SQL will translate directly to this command API.

For instance, _by default search queries are executed as a LIKE_. If you want to search for an exact match prefix your criteria with ==.

Examples
```
$commandArray = [
    'field1' => '==value1', // exactly  'value1'
    'field2' => 'value2'    // contains 'value2'
];
```

## Best Practices

See the [SimpleFM_FMServer_Sample][3] demo application which illustrates use of SimpleFM in the model layer of an MVC Zend Framework 2 application.

[1]: http://www.soliantconsulting.com
[2]: http://www.filemaker.com/products/filemaker-server/
[3]: https://getcomposer.org/doc/00-intro.md
[4]: http://www.php-fig.org/
[5]: http://framework.zend.com/
[6]: https://github.com/soliantconsulting/SimpleFM_FMServer_Sample
[7]: https://apigility.org/
