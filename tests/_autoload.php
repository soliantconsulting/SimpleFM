<?php
/**
 * Setup autoloading
 */

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    include_once __DIR__ . '/../vendor/autoload.php';
} else {
    die('Composer autoload.php is missing.');
}
