<?php
namespace Soliant\SimpleFM;

use DateTime;
use Soliant\SimpleFM\Exception\ReservedWordException;
use Soliant\SimpleFM\Exception\RuntimeException;
use Soliant\SimpleFM\Result\AbstractResult;

final class StringUtils
{
    const KNOWN_ERROR_STATE = 'Undefined variable: error_clear_last';

    /**
     * Can't use native http_build_query because it drops args with empty values like &-find
     *
     * @param $string
     * @return array
     */
    public static function explodeNameValueString($string)
    {
        $array = explode('&', $string);
        if (count($array) < 2) {
            $nameValue = explode('=', $array[0], 2);
            return self::buildCommandNameValue($nameValue);
        }
        $resultArray = [];
        foreach ($array as $item) {
            $nameValue = explode('=', $item, 2);
            $resultArray = array_merge($resultArray, self::buildCommandNameValue($nameValue));
        }
        return $resultArray;
    }

    /**
     * @param array $nameValue
     * @return array
     */
    private static function buildCommandNameValue(array $nameValue)
    {
        if (count($nameValue) < 2) {
            return [$nameValue[0] => null];
        }
        return [$nameValue[0] => $nameValue[1]];
    }

    /**
     * Can't use native http_build_query because it drops args with empty values like &-find
     *
     * @param array $commandArray
     * @return string
     */
    public static function repackCommandString(array $commandArray)
    {
        $amp = '';
        $commandString = '';
        if (!empty($commandArray)) {
            foreach ($commandArray as $name => $value) {
                if ($value instanceof DateTime) {
                    $value = $value->format('m/d/Y H:i:s');
                }
                $commandString .= ($value === null || $value === '')
                    ? $amp . urlencode($name)
                    : $amp . urlencode($name) . '=' . urlencode($value);
                $amp = '&';
            }
        }
        return $commandString;
    }

    /**
     * @param string $fieldName
     * @throws ReservedWordException
     * @return boolean
     */
    public static function fieldNameIsValid($fieldName)
    {
        $reservedNames = ['index', 'recid', 'modid'];
        if (in_array($fieldName, $reservedNames)) {
            throw new ReservedWordException(
                'SimpleFM Exception: "' . $fieldName .
                '" is a reserved word and cannot be used as a field name on any FileMaker layout used with SimpleFM.',
                $fieldName
            );
        }
        return true;
    }

    /**
     * @param $libxmlError
     * @param $xml
     * @return string
     */
    public static function displayXmlError($libxmlError, $xml)
    {
        $return = $xml[$libxmlError->line - 1] . "\n";
        $return .= str_repeat('-', $libxmlError->column) . "^\n";

        switch ($libxmlError->level) {
            case LIBXML_ERR_WARNING:
                $return .= "Warning $libxmlError->code: ";
                break;
            case LIBXML_ERR_ERROR:
                $return .= "Error $libxmlError->code: ";
                break;
            case LIBXML_ERR_FATAL:
                $return .= "Fatal Error $libxmlError->code: ";
                break;
        }

        $return .= trim($libxmlError->message) .
            "\n  Line: $libxmlError->line" .
            "\n  Column: $libxmlError->column";

        if ($libxmlError->file) {
            $return .= "\n  File: $libxmlError->file";
        }

        return "$return\n\n--------------------------------------------\n\n";
    }

    /**
     * @param string|array $error
     * @return array
     */
    public static function extractErrorFromPhpMessage($error)
    {
        if (is_array($error) && isset($error['message'])) {
            $errorString = $error['message'];
        } else {
            $errorString = $error;
        }

        /**
         * See self::errorClearLast method which puts last error in a known error state
         */
        if (null === $errorString || self::KNOWN_ERROR_STATE === $errorString) {
            return self::buildErrorArray(0, 'No Error', null);
        }

        /**
         * Capture cURL error
         */
        if (is_array($error) && isset($error['type']) && strtoupper($error['type']) === 'CURL') {
            return self::buildErrorArray($error['code'], $error['message'], 'PHP');
        }

        /**
         * Capture HTTP error
         * Most common HTTP error message to expect (line break added for clarity):
         * file_get_contents(http://10.0.0.13:80/fmi/xml/fmresultset.xml)
         * [function.file-get-contents]: failed to open stream: HTTP request failed! HTTP/1.1 401 Unauthorized
         */
        $matches = [];
        $message = preg_match('/HTTP\/[A-Za-z0-9\s\.]+/', $errorString, $matches);
        if (!empty($matches)) {
            // strip off the header prefix
            $matches = trim(str_replace('HTTP/1.1 ', '', $matches[0]));
            $result = explode(' ', $matches, 2);
            // normal case will yield an http error code in location 0 and a message in location 1
            if ((int) $result[0] != 0) {
                return self::buildErrorArray((int) $result[0], (string) $result[1], 'HTTP');
            }
            return self::buildErrorArray(null, $matches, 'HTTP');
        }

        /**
         * Default to PHP error and pass through the error string
         * example: file_get_contents throws an error if hostname does not resolve with dns
         * For lack of a better idea, we chose a cURL error code here.
         * CURLE_COULDNT_CONNECT (7): Failed to connect() to host or proxy.
         * See http://curl.haxx.se/libcurl/c/libcurl-errors.html
         */
        return self::buildErrorArray(7, $errorString, 'PHP');
    }

    /**
     * @param int $errorCode
     * @param string $errorMessage
     * @param string $errorType
     * @return array
     */
    private static function buildErrorArray($errorCode, $errorMessage, $errorType)
    {
        $return['errorCode'] = $errorCode;
        $return['errorMessage'] = $errorMessage;
        $return['errorType'] = $errorType;
        return $return;
    }

    /**
     * See http://php.net/manual/en/function.error-get-last.php
     * See https://www.mail-archive.com/internals@lists.php.net/msg76560.html
     */
    public static function errorClearLast()
    {
        $dummyCallable = function () {
            // nothing
        };

        // set a temporary error handler that does nothing
        set_error_handler($dummyCallable, 0);

        // put last error in a known state by calling an undefined variable which will return self::KNOWN_ERROR_STATE
        @$error_clear_last;

        // restore the previous error handler
        restore_error_handler();
    }

    /**
     * @param $resultClassName
     * @param $urlDebug
     * @param $errorCode
     * @param $errorMessage
     * @param $errorType
     * @return AbstractResult
     */
    public static function createResult(
        $resultClassName,
        $urlDebug,
        $errorCode,
        $errorMessage,
        $errorType
    ) {
        if (!class_exists($resultClassName)) {
            throw new RuntimeException(
                '$resultClassName must create an instance of Soliant\SimpleFM\Result\AbstractResult'
            );
        }

        /** @var AbstractResult $result */
        $result = new $resultClassName(
            $urlDebug,
            $errorCode,
            $errorMessage,
            $errorType
        );

        if (!$result instanceof AbstractResult) {
            throw new RuntimeException(
                '$resultClassName must create an instance of Soliant\SimpleFM\Result\AbstractResult'
            );
        }

        return $result;
    }
}
