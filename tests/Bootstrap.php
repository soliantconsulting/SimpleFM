<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 * 
 * @package   Soliant\SimpleFM
 * @copyright Copyright (c) 2007-2013 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

/**
 * Set error reporting to the level to which Zend Framework code must comply.
 */
error_reporting( E_ALL | E_STRICT );


if (class_exists('PHPUnit_Runner_Version', true)) {
    $phpUnitVersion = PHPUnit_Runner_Version::id();
    if ('@package_version@' !== $phpUnitVersion && version_compare($phpUnitVersion, '3.7.0', '<')) {
        echo 'This version of PHPUnit (' . PHPUnit_Runner_Version::id() . ') is not supported in SimpleFM 2.x unit tests.' . PHP_EOL;
        exit(1);
    }
    unset($phpUnitVersion);
}

/**
 * Determine the root, library, and tests directories of the framework
 * distribution.
 */
$simpleFmRoot        = realpath(dirname(__DIR__));
$simpleFmCoreLibrary = "$simpleFmRoot/library";
$simpleFmCoreTests   = "$simpleFmRoot/tests";

/**
 * Prepend the SimpleFM library/ and tests/ directories to the
 * include_path. This allows the tests to run out of the box and helps prevent
 * loading other copies of the framework code and tests that would supersede
 * this copy.
 */
$path = array(
    $simpleFmCoreLibrary,
    $simpleFmCoreTests,
    get_include_path(),
);
set_include_path(implode(PATH_SEPARATOR, $path));

/**
 * Setup autoloading
 */
include __DIR__ . '/_autoload.php';

/*
 * Load the user-defined test configuration file, if it exists; otherwise, load
 * the default configuration.
 */
if (is_readable($simpleFmCoreTests . DIRECTORY_SEPARATOR . 'TestConfiguration.php')) {
    require_once $simpleFmCoreTests . DIRECTORY_SEPARATOR . 'TestConfiguration.php';
} else {
    require_once $simpleFmCoreTests . DIRECTORY_SEPARATOR . 'TestConfiguration.php.dist';
}

if (defined('TESTS_GENERATE_REPORT') && TESTS_GENERATE_REPORT === true) {
    $codeCoverageFilter = new PHP_CodeCoverage_Filter();

    $lastArg = end($_SERVER['argv']);
    if (is_dir($simpleFmCoreTests . '/' . $lastArg)) {
        $codeCoverageFilter->addDirectoryToWhitelist($simpleFmCoreLibrary . '/' . $lastArg);
    } elseif (is_file($simpleFmCoreTests . '/' . $lastArg)) {
        $codeCoverageFilter->addDirectoryToWhitelist(dirname($simpleFmCoreLibrary . '/' . $lastArg));
    } else {
        $codeCoverageFilter->addDirectoryToWhitelist($simpleFmCoreLibrary);
    }

    /*
     * Omit from code coverage reports the contents of the tests directory
     */
    $codeCoverageFilter->addDirectoryToBlacklist($simpleFmCoreTests, '');
    $codeCoverageFilter->addDirectoryToBlacklist(PEAR_INSTALL_DIR, '');
    $codeCoverageFilter->addDirectoryToBlacklist(PHP_LIBDIR, '');

    unset($codeCoverageFilter);
}


/**
 * Start output buffering, if enabled
 */
if (defined('TESTS_ZEND_OB_ENABLED') && constant('TESTS_ZEND_OB_ENABLED')) {
    ob_start();
}

/*
 * Unset global variables that are no longer needed.
 */
unset($simpleFmRoot, $simpleFmCoreLibrary, $simpleFmCoreTests, $path);

/**
 * Internal PHP function mocks
 * To be used with runkit_function_rename()
 */
function move_uploaded_file_mock($source, $dest)
{
    return rename($source, $dest);
}
