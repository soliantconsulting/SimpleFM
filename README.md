# SimpleFM

SimpleFM is a fast, convenient and free tool designed by [Soliant Consulting, Inc.][1] to facilitate connections between PHP web applications and FileMaker Server.

SimpleFM is an ultra-lightweight PHP5 package that uses the [FileMaker Server][2] XML API. The FMS XML API is commonly referred to as Custom Web Publishing (CWP for short).

See also, the [SimpleFM_FMServer_Sample][3] demo application which illustrates use of SimpleFM in the model layer of an MVC Zend Framework 2 application.

## Features

### Easy to Integrate

* Returns a PHP array. The result parser inside SimpleFMAdapter uses PHP5's SimpleXML.
* Can be used on it's own or with any service class, such as Zend\Amf, Zend\Rest, and Zend\Soap.

### CWP Debugger

* Easily see the underlying API command formatted as a URL for easy troubleshooting
* Use the convenient errorToEnglish function to interpret FMS error codes

## Simplicity and Performance

SimpleFM is a single class with less than 1000 lines of code. While SimpleFM was written with simplicity as the main guiding principle, it also perfoms well. We have informally benchmarked it, and obtained faster results for the same queries compared to the two most common CWP PHP alternatives.

## System Requirements

SimpleFM, the examples and this documentation are tailored for PHP 5.3 and FileMaker Sever 12

* PHP 5.3+
* FileMaker Server 12+

With minimum effort, you could get them to work with PHP 5.0 (requires SimpleXML) and any version of FileMaker server that uses fmresultset.xml grammar, however, backward compatibility is not maintained.

## License

SimpleFM is free for commercial and non-commercial use, licensed under the business-friendly standard MIT license.


# SimpleFM Documentation

All the examples included with SimpleFM are based the FMServer_Sample which is included with FileMaker Server 12. To use the examples it is assumed that you have FMServer_Sample running on a FileMaker 12 host with XML web publishing enabled. (You may also have any other FMS services enabled, including PHP web publishing, but only XML is required for SimpleFM.) Setup and configuration of FileMaker server is beyond the scope of this documentation.

See /documentation/simplefm_example.php for a working PHP example that follows the basic steps shown in this Quickstart section, as well as some additional tips about usage.

## Quickstart

### Import the adapter

    use Soliant\SimpleFM\Adapter;
    
### Basic adapter configuration

    $hostParams = array(
        'hostname' => 'localhost',
        'dbname'   => 'FMServer_Sample',
        'username' => 'Admin',
        'password' => ''
    );

### Instantiate the adapter

    $adapter = new Adapter($hostParams);

### Set layout context

    $adapter->setLayoutname('Tasks');
    
    
### Set command(s)

    /**
     * @Note: See fms12_cwp_xml_en.pdf Appendix A for a complete command reference.
     * Commands that take no arguments, such as -findall, must be set with either a
     * NULL value or an empty string.
     * 
     * @WARNING: Copy/paste out of the pdf must be done with caution. The typsetting
     * uses emdash, not hyphen characters. They look very similar, and this can be
     * very hard to troubleshoot if you are not careful.
     */
    $adapter->setCommandarray(
        array(
            '-max'     => 10,
            '-skip'    => 5,
            '-findall' => NULL
        )
    );

### Execute

    $result = $adapter->execute();
    
### Handle the result

    $url       = $result['url'];           // string
    $error     = $result['error'];         // int
    $errortext = $result['errortext'];     // string
    $errortype = $result['errortype'];     // string
    $count     = $result['count'];         // int
    $fetchsize = $result['fetchsize'];     // int
    $rows      = $result['rows'];          // array
    
## About FileMaker Portals

Portals are returned as named child arrays to every record in the fetched set. Be careful about adding portals to your web layouts, as all associations are loaded eagerly, and this could bloat your result array. SimpleFM can't take advantage of techniques like lazy loading that help mitigate performance issues with large related data sets in ORMs like Doctrine, so it is up to the developer to tailor the web layouts appropriately for best performance.

There can be more than one portal on a layout. SimpleFM returns n portals for every record in the found set.

Assuming you leave rowsbyrecid as FALSE (the default setting), here is example array notation that would echo the the recid and the field value from the first portal row on the first record in the result set. Note that index, recid and modid are always properties on every parent and child row.

    echo $rows[0]['Portal_TO_Name']['rows'][0]['recid'].'<br/>';    
    echo $rows[0]['Portal_TO_Name']['rows'][0]['myField'];
 
If you set rowsbyrecid to TRUE on your adapter, here is syntax that would echo the data from a portal where the parent row has recid 154 and the child row has recid 335932.

    echo $rows[154]['Portal_TO_Name']['rows'][335932]['Related_TO_Name::fieldOnPortal'];

It is left to you do decide which way you want the results indexed.

## Best Practices

See the [SimpleFM_FMServer_Sample][3] demo application which illustrates use of SimpleFM in the model layer of an MVC Zend Framework 2 application.

[1]: http://www.soliantconsulting.com
[2]: http://www.filemaker.com/products/filemaker-server/
[3]: https://github.com/soliantconsulting/SimpleFM_FMServer_Sample
