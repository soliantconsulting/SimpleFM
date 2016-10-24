<?php
/**
 * FileMaker API for PHP
 *
 * @package FileMaker
 *
 * Copyright © 2005-2007, FileMaker, Inc. All rights reserved.
 * NOTE: Use of this source code is subject to the terms of the FileMaker
 * Software License which accompanies the code. Your use of this source code
 * signifies your agreement to such license terms and conditions. Except as
 * expressly granted in the Software License, no other copyright, patent, or
 * other intellectual property license or right is granted, either expressly or
 * by implication, by FileMaker.
 */

/**#@+
 * @ignore Always load the error class and the implementation delegate.
 */
require_once dirname(__FILE__) . '/FileMaker/Error.php';
require_once dirname(__FILE__) . '/FileMaker/Implementation/FileMakerImpl.php';
/**#@-*/

/**#@+
 * Find constants.
 */
define('FILEMAKER_FIND_LT', '<');
define('FILEMAKER_FIND_LTE', '<=');
define('FILEMAKER_FIND_GT', '>');
define('FILEMAKER_FIND_GTE', '>=');
define('FILEMAKER_FIND_RANGE', '...');
define('FILEMAKER_FIND_DUPLICATES', '!');
define('FILEMAKER_FIND_TODAY', '//');
define('FILEMAKER_FIND_INVALID_DATETIME', '?');
define('FILEMAKER_FIND_CHAR', '@');
define('FILEMAKER_FIND_DIGIT', '#');
define('FILEMAKER_FIND_CHAR_WILDCARD', '*');
define('FILEMAKER_FIND_LITERAL', '""');
define('FILEMAKER_FIND_RELAXED', '~');
define('FILEMAKER_FIND_FIELDMATCH', '==');
/**#@-*/

/**#@+
 * Find logical operator constants.
  * Use with the {@link FileMaker_Command_Find::setLogicalOperator()}  
 * method.
*/
define('FILEMAKER_FIND_AND', 'and');
define('FILEMAKER_FIND_OR', 'or');
/**#@-*/

/**#@+
 * Pre-validation rule constants.
 */
define('FILEMAKER_RULE_NOTEMPTY', 1);
define('FILEMAKER_RULE_NUMERICONLY', 2);
define('FILEMAKER_RULE_MAXCHARACTERS', 3);
define('FILEMAKER_RULE_FOURDIGITYEAR', 4);
define('FILEMAKER_RULE_TIMEOFDAY', 5);
define('FILEMAKER_RULE_TIMESTAMP_FIELD', 6);
define('FILEMAKER_RULE_DATE_FIELD', 7);
define('FILEMAKER_RULE_TIME_FIELD', 8);
/**#@-*/

/**#@+
 * Sort direction constants. 
 * Use with the {@link FileMaker_Command_Find::addSortRule()} and
 * {@link FileMaker_Command_CompoundFind::addSortRule()} methods.
 */
define('FILEMAKER_SORT_ASCEND', 'ascend');
define('FILEMAKER_SORT_DESCEND', 'descend');
/**#@-*/

/**#@+
 * Logging level constants.
 */
define('FILEMAKER_LOG_ERR', 3);
define('FILEMAKER_LOG_INFO', 6);
define('FILEMAKER_LOG_DEBUG', 7);
/**#@-*/

/**
 * Base FileMaker class. Defines database properties, connects to a database, 
 * and gets information about the API.
 *
 * @package FileMaker
 */
class FileMaker
{
    /**
     * Implementation. This is the object that actually implements the API.
     *
     * @var FileMaker_Implementation
     * @access private
     */
    var $_impl;

    /**
     * Tests whether a variable is a FileMaker API Error.
     *
     * @param mixed $variable Variable to test.
     * @return boolean TRUE, if the variable is a {@link FileMaker_Error} object.
     * @static
     *
     */
    function isError($variable)
    {
        return is_a($variable, 'FileMaker_Error');
    }

    /**
     * Returns the version of the FileMaker API for PHP.
     *
     * @return string API version.
     * @static
     */
    function getAPIVersion()
    {
        return FileMaker_Implementation::getAPIVersion();
    }

    /**
     * Returns the minimum version of FileMaker Server that this API works with.
     *
     * @return string Minimum FileMaker Server version.
     * @static
     */
    function getMinServerVersion()
    {
        return FileMaker_Implementation::getMinServerVersion();
    }

    /**
     * FileMaker object constructor. 
     * 
     * If you want to use the constructor without specifying all the 
     *  parameters, pass in NULL for the parameters you want to omit. 
     * For example, to specify only the database name, username, and 
     * password, but omit the hostspec, call the constructor as follows:
     *  
     * <samp>
     * new FileMaker('DatabaseName', NULL, 'username', 'password');
     * </samp>
     * 
     * @param string $database Name of the database to connect to.
     * @param string $hostspec Hostspec of web server in FileMaker Server 
     *        deployment. Defaults to http://localhost, if set to NULL.
     * @param string $username Account name to log into database.
     * @param string $password Password for account.
     */
    function FileMaker($database = NULL, $hostspec = NULL, $username = NULL, $password = NULL)
    {
        $this->_impl = new FileMaker_Implementation($database, $hostspec, $username, $password);
    }

    /**
     * Sets a property to a new value for all API calls.
     *
     * @param string $prop Name of the property to set.
     * @param string $value Property's new value.
     */
    function setProperty($prop, $value)
    {
        $this->_impl->setProperty($prop, $value);
    }

    /**
     * Returns the current value of a property.
     *
     * @param string $prop Name of the property.
     *
     * @return string Property's current value.
     */
    function getProperty($prop)
    {
        return $this->_impl->getProperty($prop);
    }

    /**
     * Returns an associative array of property name => property value for
     * all current properties and their current values. 
     *
     * This array enables PHP object introspection and debugging when necessary.
     *
     * @return array All current properties.
     */
    function getProperties()
    {
        return $this->_impl->getProperties();
    }

    /**
     * Associates a PEAR Log object with the API for logging requests
     * and responses.
     *
     * @param Log &$logger PEAR Log object.
     */
    function setLogger(&$logger)
    {
        $this->_impl->setLogger($logger);
    }

    /**
     * Creates a new FileMaker_Command_Add object.
     *
     * @param string $layout Layout to add a record to.
     * @param array $values Associative array of field name => value pairs. 
     *        To set field repetitions, use a numerically indexed array for 
     *        the value of a field, with the numeric keys corresponding to the 
     *        repetition number to set.
     *
     * @return FileMaker_Command_Add New Add command object.
     */
    function &newAddCommand($layout, $values = array())
    {
        return $this->_impl->newAddCommand($layout, $values);
    }

    /**
     * Creates a new FileMaker_Command_Edit object.
     *
     * @param string $layout Layout that the record is part of.
     * @param string $recordId ID of the record to edit.
     * @param array $updatedValues Associative array of field name => value 
     *        pairs that contain the updated field values. To set field 
     *        repetitions, use a numerically indexed array for the value of a 
     *        field, with the numeric keys corresponding to the repetition 
     *        number to set.
     *
     * @return FileMaker_Command_Edit New Edit command object.
     */
    function &newEditCommand($layout, $recordId, $updatedValues = array())
    {
        return $this->_impl->newEditCommand($layout, $recordId, $updatedValues);
    }

    /**
     * Creates a new FileMaker_Command_Delete object.
     *
     * @param string $layout Layout to delete record from.
     * @param string $recordId ID of the record to delete.
     *
     * @return FileMaker_Command_Delete New Delete command object.
     */
    function &newDeleteCommand($layout, $recordId)
    {
        return $this->_impl->newDeleteCommand($layout, $recordId);
    }

    /**
     * Creates a new FileMaker_Command_Duplicate object.
     *
     * @param string $layout Layout that the record to duplicate is in.
     * @param string $recordId ID of the record to duplicate.
     *
     * @return FileMaker_Command_Duplicate New Duplicate command object.
     */
    function &newDuplicateCommand($layout, $recordId)
    {
        return $this->_impl->newDuplicateCommand($layout, $recordId);
    }

    /**
     * Creates a new FileMaker_Command_Find object.
     *
     * @param string $layout Layout to find records in.
     *
     * @return FileMaker_Command_Find New Find command object.
     */
    function &newFindCommand($layout)
    {
        return $this->_impl->newFindCommand($layout);
    }

    /**
     * 
     * Creates a new FileMaker_Command_CompoundFind object.
     *
     * @param string $layout Layout to find records in.
     *
     * @return FileMaker_Command_CompoundFind New Compound Find Set command 
     *         object.
     */
    function &newCompoundFindCommand($layout)
    {
        return $this->_impl->newCompoundFindCommand($layout);
    }
    
     /**
     * 
     * Creates a new FileMaker_Command_FindRequest object. Add one or more 
     * Find Request objects to a {@link FileMaker_Command_CompoundFind} object, 
     * then execute the Compound Find command.
     *
     * @param string $layout Layout to find records in.
     *
     * @return FileMaker_Command_FindRequest New Find Request command object.
     */
    function &newFindRequest($layout)
    {
        return $this->_impl->newFindRequest($layout);
    }
    
    /**
     * Creates a new FileMaker_Command_FindAny object.
     *
     * @param string $layout Layout to find one random record from.
     *
     * @return FileMaker_Command_FindAny New Find Any command object.
     */
    function &newFindAnyCommand($layout)
    {
        return $this->_impl->newFindAnyCommand($layout);
    }

    /**
     * Creates a new FileMaker_Command_FindAll object.
     *
     * @param string $layout Layout to find all records in.
     *
     * @return FileMaker_Command_FindAll New Find All command object.
     */
    function &newFindAllCommand($layout)
    {
        return $this->_impl->newFindAllCommand($layout);
    }

    /**
     * Creates a new FileMaker_Command_PerformScript object.
     *
     * @param string $layout Layout to use for script context.
     * @param string $scriptName Name of the ScriptMaker script to run.
     * @param string $scriptParameters Any parameters to pass to the script.
     *
     * @return FileMaker_Command_PerformScript New Perform Script command 
     *         object.
     */
    function &newPerformScriptCommand($layout, $scriptName, $scriptParameters = null)
    {
        return $this->_impl->newPerformScriptCommand($layout, $scriptName, $scriptParameters);
    }

    /**
     * Creates a new FileMaker_Record object. 
     * 
     * This method does not save the new record to the database. 
     * The record is not created on the Database Server until you call 
     * this record's commit() method. You must specify a layout name, 
     * and you can optionally specify an array of field values. 
     * Individual field values can also be set in the new record object.
     * 
     *
     * @param string $layout Layout to create a new record for.
     * @param array $fieldValues Initial values for the new record's fields.
     *
     * @return FileMaker_Record New Record object.
     */
    function &createRecord($layout, $fieldValues = array())
    {
        return $this->_impl->createRecord($layout, $fieldValues);
    }

    /**
     * Returns a single FileMaker_Record object matching the given
     * layout and record ID, or a FileMaker_Error object, if this operation
     * fails.
     *
     * @param string $layout Layout that $recordId is in.
     * @param string $recordId ID of the record to get.
     *
     * @return FileMaker_Record|FileMaker_Error Record or Error object.
     */
    function &getRecordById($layout, $recordId)
    {
        return $this->_impl->getRecordById($layout, $recordId);
    }

    /**
     * Returns a Layout object that describes the specified layout.
     *
     * @param string $layout Name of the layout to describe.
     *
     * @return FileMaker_Layout|FileMaker_Error Layout or Error object.
     */
    function &getLayout($layout)
    {
        return $this->_impl->getLayout($layout);
    }

    /**
     * Returns an array of databases that are available with the current
     * server settings and the current user name and password
     * credentials.
     *
     * @return array|FileMaker_Error List of database names or an Error object.
     */
    function listDatabases()
    {
        return $this->_impl->listDatabases();
    }

    /**
     * Returns an array of ScriptMaker scripts from the current database that 
     * are available with the current server settings and the current user 
     * name and password credentials.
     *
     * @return array|FileMaker_Error List of script names or an Error object.
     */
    function listScripts()
    {
        return $this->_impl->listScripts();
    }

    /**
     * Returns an array of layouts from the current database that are
     * available with the current server settings and the current
     * user name and password credentials.
     *
     * @return array|FileMaker_Error List of layout names or an Error object.
     */
    function listLayouts()
    {
        return $this->_impl->listLayouts();
    }

    /**
     * Returns the data for the specified container field.
     * Pass in a URL string that represents the file path for the container 
     * field contents. For example, get the image data from a container field 
     * named 'Cover Image'. For a FileMaker_Record object named $record, 
     * URL-encode the path returned by the getField() method.  For example:
     * 
     * <samp>
     * <IMG src="img.php?-url=<?php echo urlencode($record->getField('Cover Image')); ?>">
     * </samp>
     * 
     * Then as shown below in a line from img.php, pass the URL into 
     * getContainerData() for the FileMaker object named $fm:
     * 
     * <samp>
     * echo $fm->getContainerData($_GET['-url']);
     * </samp>
     *
     * @param string $url URL of the container field contents to get.
     *
     * @return string Raw field data|FileMaker_Error if remote container field.
     */
    function getContainerData($url)
    {
        return $this->_impl->getContainerData($url);
    }

    /**
     * Returns the fully qualified URL for the specified container field.
     * Pass in a URL string that represents the file path for the container 
     * field contents. For example, get the URL for a container field 
     * named 'Cover Image'.  For example:
     * 
     * <samp>
     * <IMG src="<?php echo $fm->getContainerDataURL($record->getField('Cover Image')); ?>">
     * </samp>
     *
     * @param string $url URL of the container field contents to get.
     *
     * @return string Fully qualified URL to container field contents
     */
    function getContainerDataURL($url)
    {
        return $this->_impl->getContainerDataURL($url);
    }
}
