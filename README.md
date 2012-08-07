# SimpleFM

SimpleFM is a fast, convenient and free tool designed by [Soliant Consulting, Inc.][1] to facilitate connections between PHP web applications and FileMaker Server.

SimpleFM is an ultra-lightweight PHP5 package that uses the [FileMaker Server][2] XML API. The FMS XML API is commonly referred to as Custom Web Publishing (CWP for short). The SimpleFM package consists of two classes, an adapter and a proxy.

## Classes

SimpleFMProxy allows for easy creation of technology-neutral services which accept inputs via standard HTTP request. This makes it very convenient to set up reverse proxying so the FMS can be kept off the WAN for enhanced security. Services can easily be built to accept web forms, Ajax calls, etc. Examples are included, based on the example data file that ships with every copy of FMS.

SimpleFMAdapter handles database connections and returns a standard PHP object result. The XML parser inside SimpleFMAdapter uses PHP5's SimpleXML functions. The adapter can be used with the included SimpleFMProxy class, or with other service classes, such as Zend_Amf, Zend_Rest, and Zend_Soap.

## Features

SimpleFMAdapter offers some convenient CWP debugging features, allowing the developer to easily see the underlying API command formatted as a URL for easy troubleshooting and providing a convenient errorToEnglish function for FMS error codes.

## Simplicity

SimpleFM is under 1000 lines of code (including examples), is object oriented and takes full advantage of PHP5. While SimpleFM was written with simplicity as the main guiding principle, not performance, we have informally benchmarked it, and obtained faster results for the same queries compared to the two most common CWP PHP alternatives.

## License

SimpleFM is free for commercial and non-commercial use, licensed under the business-friendly standard MIT license.


[1]: http://www.soliantconsulting.com
[2]: http://www.filemaker.com/products/filemaker-server/